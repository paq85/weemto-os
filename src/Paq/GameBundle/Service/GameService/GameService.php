<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service\GameService;


use Doctrine\ORM\NoResultException;
use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\QuestionRepository;
use Paq\GameBundle\Entity\Repo;
use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Entity\UserScoreLog;
use Paq\GameBundle\Entity\UserScoreLogRepository;
use Paq\GameBundle\Model\UserScoreLogListenerInterface;
use Ramsey\Uuid\Uuid;

class GameService implements GameServiceInterface
{
    /**
     * What's the probability in percents that given Tag should be excluded when picking next Question?
     *
     * @var int[]
     *  [string TagEnum => int]
     */
    private $tagExcludingProbabilityPercent = [
        TagEnum::_GENERATED_POLISH_PROVINCE => 90,
        TagEnum::_GENERATED_COUNTRY => 95,
        TagEnum::_GENERATED_MATH => 90
    ];

    /**
     * @var QuestionRepository
     */
    private $questionRepo;

    /**
     * @var UserScoreLogListenerInterface
     */
    private $userScoreLogListener;

    public function __construct(QuestionRepository $questionRepo)
    {
        $this->questionRepo = $questionRepo;
    }

    /**
     * @param UserScoreLogListenerInterface|null $listener
     */
    public function setUserScoreLogRepository(UserScoreLogListenerInterface $listener = null)
    {
        $this->userScoreLogListener = $listener;
    }

    /**
     * @inheritdoc
     */
    public function onNewGame(Game $game)
    {
        $this->onGameReset($game);
    }

    /**
     * @inheritdoc
     */
    public function onUserAdded(Game $game, User $user)
    {
        $game->addUser($user);
    }

    /**
     * @inheritdoc
     */
    public function onUserDisconnect(Game $game, User $user)
    {
        $game->removeUser($user);

        $this->updateGameState($game);
    }

    /**
     * @inheritdoc
     */
    public function onUserAnswers(Game $game, User $user, $questionId, $answer)
    {
        if (!is_int($questionId)) {
            throw new \InvalidArgumentException('Question ID must be an integer');
        }

        if ($game->isFinished()) {
            return;
        }

        if (! $game->hasCurrentQuestion() || $game->getCurrentQuestion()->getId() !== $questionId) {
            // only answers for current Question are being processed and stored
            return;
        }

        $game->setUserCurrentAnswer($user, $answer);

        $this->updateGameState($game);
    }

    /**
     * @inheritdoc
     */
    public function onGameReset(Game $game)
    {
        $game->clearCurrentScores();
        $game->clearCurrentAnswers();
        $game->startNextRound();
        $this->setNextQuestion($game);
    }

    /**
     * @inheritdoc
     */
    public function onGameNextQuestion(Game $game)
    {
        // for all users that have not answered yet set all answers to wrong, update Game state and switch question
        if (! $game->allUsersHaveAnswered()) {
            foreach ($game->getUsers() as $iUser) {
                if (! $game->hasUserCurrentAnswer($iUser)) {
                    $game->setUserCurrentAnswer($iUser, '?');
                }
            }
        }

        $this->updateGameState($game);
    }

    /**
     * @inheritdoc
     */
    public function onTagsSelected(Game $game, $tags)
    {
        $game->setTags($tags);

        $this->onGameReset($game);
    }

    /**
     * Update Game state to reflect current answers and Users
     * If all Users have answered and Game is not finished then switch to next Question
     *
     * @param Game $game
     */
    private function updateGameState(Game $game)
    {
        if ($game->allUsersHaveAnswered()) {
            $currentQuestion = $game->getCurrentQuestion();
            $dateTime = new \DateTime();
            $userScoreLogs = [];

            /* @var User $iUser */
            foreach ($game->getUsers() as $iUser) {
                $pointsWon = $this->getHowMuchUserScores($game, $iUser);
                $game->setUserScore($iUser, $game->getUserCurrentScore($iUser) + $pointsWon);
                if (!$iUser->hasRole(User::ROLE_TEMPORAL)) {
                    $userScoreLogs[] = new UserScoreLog(Uuid::uuid4(), $iUser, $game->getId(), $currentQuestion, $pointsWon, $dateTime);
                }
            }

            $this->onNewUserScoreLogs($userScoreLogs);

            if (! $game->isFinished()) {
                $this->setNextQuestion($game);
            }
        }
    }

    /**
     * Computes how many points should User get for his current answer
     *
     * @param User $user
     * @return int
     */
    private function getHowMuchUserScores(Game $game, User $user)
    {
        if ($game->hasUserProperAnswer($user)) {
            /**
             * 1 point for good answer + X for each player who answered wrong + Y for each player answered correct but after this user
             */
            $correctAnswerPoints = 1;
            $userWithWrongAnswerCount = $game->countWrongAnswers();
            $userWithProperAnswerButSlowerCount = $game->countProperAnswersSlowerThan($user);
            return $correctAnswerPoints + $userWithWrongAnswerCount + $userWithProperAnswerButSlowerCount;
        } else {
            return 0;
        }
    }

    /**
     * @param Game $game
     */
    private function setNextQuestion(Game $game)
    {
        $nextQuestion = $this->getNextQuestion($game);
        $game->setCurrentQuestion($nextQuestion);
    }

    /**
     * Based on a various conditions return the next, suggested Question for given Game
     *
     * @param Game $game
     * @return \Paq\GameBundle\Entity\Question|null Next Question appropriate for given Game or NULL if no
     *  appropriate Question could be found
     */
    private function getNextQuestion(Game $game)
    {
        $options = [];
        $options['excluded_tag_names'] = $this->getTagIdsExcludedInPurpose();

        if ($game->hasCurrentQuestion()) {
            // do not show same questions again
            $options['excluded_ids'] = $game->getQuestionsLog();
        }

        // show only questions from Tags preferred by User (set for the Game)
        $options['included_tag_ids'] = Repo::getIds($game->getTags());

        try {
            return $this->questionRepo->getRandomQuestion($options);
        } catch (NoResultException $nrex) {
            return null;
        } catch (\Exception $ex) {
            throw new \Exception('Could not get next Question. ' . $ex->getMessage(), null, $ex);
        }
    }

    /**
     * For some reason we do NOT want some Tags to be used, eg. they are not so attractive
     * This method returns Tag IDs that should be excluded when picking next Question
     */
    private function getTagIdsExcludedInPurpose()
    {
        $excludedTagIds = [];

        foreach ($this->tagExcludingProbabilityPercent as $tagName => $exclusionProbability) {
            $rand = rand(0, 100);
            if ($rand < $exclusionProbability) {
                $excludedTagIds[] = $tagName;
            }
        }

        return $excludedTagIds;
    }

    /**
     * @param UserScoreLog[] $logs
     */
    private function onNewUserScoreLogs($logs)
    {
        if (null !== $this->userScoreLogListener) {
            $this->userScoreLogListener->onNewUserScoreLogs($logs);
        }
    }
} 
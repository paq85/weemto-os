<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Version;
use JMS\Serializer\Annotation\VirtualProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Paq\Pro\String\Strings;

/**
 * @ORM\Entity(repositoryClass="Paq\GameBundle\Entity\GameRepository")
 * @ORM\Table(
 *  indexes={@Index(name="GameSessionIdx", columns={"sessionId"})},
 *  uniqueConstraints={@ORM\UniqueConstraint(name="GameGcodeUniqueIdx", columns={"gcode"})}
 * )
 */
class Game
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

    /**
     * How many correct answers Users must give before game is finished?
     */
    const QUESTIONS_LIMIT = 20;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Version
     * @ORM\Column(type="integer")
     * @var int
     */
    private $version;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $gcode;

    /**
     * @ManyToOne(targetEntity="Paq\GameBundle\Entity\User")
     * @JoinColumn(name="createdBy", referencedColumnName="id", onDelete="SET NULL")
     *
     * @var User
     */
    private $createdBy;

    /**
     * // type="binary" ?
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $sessionId;

    /**
     * @ManyToMany(targetEntity="Paq\GameBundle\Entity\User")
     * @JoinTable(name="GameHasUsers",
     *      joinColumns={@JoinColumn(name="gameId", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@JoinColumn(name="userId", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     */
    private $users;

    /**
     * @ManyToOne(targetEntity="Paq\GameBundle\Entity\Question")
     * @JoinColumn(referencedColumnName="id", onDelete="SET NULL")
     */
    private $currentQuestion;

    /**
     * @ORM\Column(type="array")
     * @var int[] QuestionHint Ids
     */
    private $currentQuestionHintsOrder;

    /**
     * @ORM\Column(type="json_array")
     * @var string[] where key = Player.id, and value = Player's answer for current Question
     */
    private $currentAnswers;

    /**
     * Array with IDs of users which have answered; In the ascending order of their answering time.
     * @ORM\Column(type="array")
     * @var int[]
     */
    private $userAnswersLog;

    /**
     * @ORM\Column(type="json_array")
     * @var int[] where key = Player.id, and value = Player's score
     */
    private $currentScores;

    /**
     * @ORM\Column(type="json_array")
     * @var int[] IDs of Questions that were used for this Game
     */
    private $questionsLog;

    /**
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $roundQuestionCountLimit;

    /**
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $roundNumber = 0;

    /**
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $roundQuestionNumber = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Paq\GameBundle\Entity\Tag")
     * @ORM\JoinTable(name="GameHasTags",
     *  joinColumns={@ORM\JoinColumn(name="gameId", referencedColumnName="id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="tagId", referencedColumnName="id", onDelete="CASCADE")}
     *  )
     * @var Tag[]|ArrayCollection
     */
    private $tags;

    /**
     * @param int $questionsLimit
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->currentQuestionHintsOrder = [];
        $this->currentAnswers = [];
        $this->userAnswersLog = [];
        $this->currentScores = [];
        $this->questionsLog = [];
        $this->setRoundQuestionCountLimit(self::QUESTIONS_LIMIT);
        $this->tags = new ArrayCollection();
        $this->version = 1;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $gcode
     */
    public function setGCode($gcode)
    {
        $this->gcode = $gcode;
    }

    /**
     * @return string
     */
    public function getGCode()
    {
        return $this->gcode;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isCreatedBy(User $user)
    {
        return ($user->getId() === $this->createdBy->getId());
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Add users
     *
     * @param \Paq\GameBundle\Entity\User $user
     * @return Game
     */
    public function addUser(\Paq\GameBundle\Entity\User $user)
    {
        if (!$this->users->contains($user)) {
            if (count($this->users) === 0) {
                $this->createdBy = $user;
            }

            $this->users[] = $user;
            $this->setUserCurrentAnswer($user, null);
            $this->setUserScore($user, 0);
        }

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Paq\GameBundle\Entity\User $players
     */
    public function removeUser(\Paq\GameBundle\Entity\User $user)
    {
        if ($this->users->contains($user)) {
            $removedUserId = $this->getUserId($user);
            $this->users->removeElement($user);
            unset($this->currentAnswers[$removedUserId]);
            $this->userAnswersLog = array_filter($this->userAnswersLog, function($iUserId) use ($removedUserId) {
                return ($removedUserId !== $iUserId);
            });
            unset($this->currentScores[$removedUserId]);
        }
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }

    /**
     * Set currentQuestion
     *
     * @param \Paq\GameBundle\Entity\Question $currentQuestion
     * @return Game
     */
    public function setCurrentQuestion(\Paq\GameBundle\Entity\Question $currentQuestion = null)
    {
        $this->currentQuestion = $currentQuestion;

        if (null !== $currentQuestion) {
            $this->roundQuestionNumber++;
            $this->logQuestion($currentQuestion);
            $this->randomizeCurrentQuestionHintsOrder();
        }

        $this->clearCurrentAnswers();

        return $this;
    }

    /**
     * Get currentQuestion
     *
     * @return \Paq\GameBundle\Entity\Question 
     */
    public function getCurrentQuestion()
    {
        return $this->currentQuestion;
    }

    /**
     * @return bool
     */
    public function hasCurrentQuestion()
    {
        return (null !== $this->currentQuestion);
    }

    /**
     * Set a random order for Question Hints for this Game's current Question
     */
    private function randomizeCurrentQuestionHintsOrder()
    {
        $this->currentQuestionHintsOrder = Repo::getIds($this->getCurrentQuestion()->getHints());
        shuffle($this->currentQuestionHintsOrder);
    }

    /**
     * @return \int[] QuestionHint IDs
     */
    public function getCurrentQuestionHintsOrder()
    {
        return $this->currentQuestionHintsOrder;
    }

    /**
     * Get currentAnswers
     *
     * @return array 
     */
    public function getCurrentAnswers()
    {
        return $this->currentAnswers;
    }

    /**
     * Set all answers to null
     */
    public function clearCurrentAnswers()
    {
        $userIds = array_keys($this->getCurrentAnswers());
        $this->currentAnswers = array_fill_keys($userIds, null);
        $this->userAnswersLog = [];
    }

    /**
     * @param User $user
     * @param string|null $answer text representing User's answer or null if no answer provided yet
     */
    public function setUserCurrentAnswer(User $user, $answer)
    {
        $this->requireUser($user);

        if ($this->hasUserCurrentAnswer($user) && $this->getUserCurrentAnswer($user) === $answer) {
            // Do not update User's anwer if it has not changed
        } else {
            $currentAnswer = ($answer === null) ? null : (string) $answer;
            $this->currentAnswers[$this->getUserId($user)] = $currentAnswer;
            if ($currentAnswer !== null) {
                // if User have already answered and is changing his answer he will be moved to the last position in the log
                $currentLogPosition = array_search($user->getId(), $this->userAnswersLog);
                if ($currentLogPosition !== false) {
                    unset($this->userAnswersLog[$currentLogPosition]);
                }
                $this->userAnswersLog[] = $user->getId();
            }
        }
    }

    /**
     * @param User $user
     * @return string|null
     */
    public function getUserCurrentAnswer(User $user)
    {
        $this->requireUser($user);

        $userId = $this->getUserId($user);
        if (!isset($this->currentAnswers[$userId])) {
            $this->currentAnswers[$userId] = null;
        }

        return $this->currentAnswers[$userId];
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasUserCurrentAnswer(User $user)
    {
        $this->requireUser($user);

        return (null !== $this->getUserCurrentAnswer($user));
    }

    /**
     * @param User $user
     * @return bool true if user's answer is correct for current Question; false otherwise - also when current Question is not set
     */
    public function hasUserProperAnswer(User $user)
    {
        $this->requireUser($user);

        if (! $this->hasCurrentQuestion()) {
            return false;
        }

        return (Strings::removeNewLine($this->getCurrentQuestion()->getProperAnswer()) === Strings::removeNewLine($this->getUserCurrentAnswer($user)));
    }

    /**
     * @return int how many Users have answered incorrect for the current Question
     */
    public function countWrongAnswers()
    {
        $wrongAnswersCount = 0;
        foreach ($this->getUsers() as $iUser) {
            if (! $this->hasUserProperAnswer($iUser)) {
                ++$wrongAnswersCount;
            }
        }

        return $wrongAnswersCount;
    }

    /**
     * How many User have answered correct but slower?
     *
     * @param User $user
     * @return int
     */
    public function countProperAnswersSlowerThan(User $user)
    {
        $this->requireUser($user);

        if (! $this->hasUserProperAnswer($user)) {
            // this User have not answered correct so no one was slower
            return 0;
        }

        $properAnswersSlower = 0;
        $userAnsweredAsThe = $this->getUserAnsweredAsThe($user);
        foreach ($this->getUsers() as $iUser) {
            $iUserAnsweredAsThe = $this->getUserAnsweredAsThe($iUser);
            if ($this->hasUserProperAnswer($iUser) && $userAnsweredAsThe < $iUserAnsweredAsThe) {
                ++$properAnswersSlower;
            }
        }

        return $properAnswersSlower;
    }

    /**
     * @param User $user
     * @return int|false
     */
    public function getUserAnsweredAsThe(User $user)
    {
        $this->requireUser($user);

        $userAnsweredAs = array_search($user->getId(), $this->userAnswersLog);
        if (false === $userAnsweredAs) {
            // User have not answered yet so he's the last one(s)
            return PHP_INT_MAX;
        } else {
            return $userAnsweredAs;
        }
    }

    /**
     * @return bool
     */
    public function allUsersHaveAnswered()
    {
        foreach ($this->getCurrentAnswers() as $answer) {
            if ($answer === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set currentScores
     *
     * @param array $currentScores
     * @return Game
     */
    public function setCurrentScores($currentScores)
    {
        $this->currentScores = $currentScores;

        return $this;
    }

    /**
     * Get currentScores
     *
     * @return array 
     */
    public function getCurrentScores()
    {
        return $this->currentScores;
    }

    /**
     * Sets current scores to 0 - for all Users
     */
    public function clearCurrentScores()
    {
        $userIds = array_keys($this->getCurrentScores());
        $this->currentScores = array_fill_keys($userIds, 0);
    }

    /**
     * @param User $user
     * @param int $score
     */
    public function setUserScore(User $user, $score)
    {
        $this->requireUser($user);

        $this->currentScores[$this->getUserId($user)] = (int) $score;
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUserCurrentScore(User $user)
    {
        $this->requireUser($user);

        return $this->currentScores[$this->getUserId($user)];
    }

    /**
     * Get IDs of Questions used for this Game
     *
     * @return array|\int[]
     */
    public function getQuestionsLog()
    {
        return $this->questionsLog;
    }

    /**
     * @param int $roundQuestionCountLimit
     */
    public function setRoundQuestionCountLimit($roundQuestionCountLimit)
    {
        $this->roundQuestionCountLimit = (int) $roundQuestionCountLimit;
    }

    /**
     * @return int
     */
    public function getRoundQuestionCountLimit()
    {
        return (null === $this->roundQuestionCountLimit) ? self::QUESTIONS_LIMIT : $this->roundQuestionCountLimit;
    }

    /**
     * @return int
     */
    public function getRoundNumber()
    {
        return $this->roundNumber;
    }

    /**
     * @return int
     */
    public function getRoundQuestionNumber()
    {
        return $this->roundQuestionNumber;
    }

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return bool
     * @VirtualProperty
     */
    public function isFinished()
    {
        if (! $this->hasUser($this->getCreatedBy())) {
            // Game creator left the Game - it's finished
            return true;
        }

        return ($this->getRoundQuestionCountLimit() <= $this->roundQuestionNumber && $this->allUsersHaveAnswered());
    }

    /**
     * Start next round
     *
     * NOTE: remember to set the current Question for new round
     */
    public function startNextRound()
    {
        $this->roundNumber++;
        $this->roundQuestionNumber = 0;
    }

    /**
     * @return string
     */
    public function computeETag()
    {
        return md5($this->version);
    }

    /**
     * @param User $user
     * @return int
     * @throws \InvalidArgumentException if somehow User does not have an ID set
     */
    private function getUserId(User $user)
    {
        if (null === $user->getId()) {
            throw new \InvalidArgumentException('User ID must be specified.');
        }

        return $user->getId();
    }

    private function logQuestion(Question $question)
    {
        $this->questionsLog[] = $question->getId();
    }

    /**
     * Make sure given User is in this Game
     *
     * @param User $user
     * @throws \InvalidArgumentException if User is not in this Game
     */
    private function requireUser(User $user)
    {
        if (! $this->hasUser($user)) {
            throw new \InvalidArgumentException('User [ ' . $user->getId() . '] is not in the Game [' . $this->getId() . ']');
        }
    }
}

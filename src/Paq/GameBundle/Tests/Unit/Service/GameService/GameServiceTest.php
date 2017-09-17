<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Service\GameService;


use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Entity\QuestionRepository;
use Paq\GameBundle\Entity\Repo;
use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Entity\User;
use Paq\GameBundle\Service\GameService\GameService;

class GameServiceTest extends \PHPUnit_Framework_TestCase
{
    const WRONG_ANSWER = 'Wrong answer';
    const QUESTION_1_PROPER_ANSWER = 'He does not go to school yet.';

    /**
     * @var User[]
     */
    private $users;

    /**
     * @var Question[]
     */
    private $questions;

    /**
     * @var Game[]
     */
    private $games;

    /**
     * @var Tag[]
     */
    private $tags;

    public function setUp()
    {
        $tag = new Tag(TagEnum::POLAND);
        $tag->setId(1);
        $this->tags[] = $tag;
        $tag = new Tag(TagEnum::CINEMA);
        $tag->setId(2);
        $this->tags[] = $tag;
        $tag = new Tag(TagEnum::ANIMALS);
        $tag->setId(3);
        $this->tags[] = $tag;
        $tag = new Tag(TagEnum::GEOGRAPHY);
        $tag->setId(4);
        $this->tags[] = $tag;

        $this->users[1] = new User();
        $this->users[1]->setId(1);
        $this->users[1]->setUsername('user1');
        $this->users[1]->setEmail('user1@foo.bar');
        $this->users[1]->getPlainPassword('user1pass');
        $this->users[2] = new User();
        $this->users[2]->setId(2);
        $this->users[2]->setUsername('user2');
        $this->users[2]->setEmail('user2@foo.bar');
        $this->users[2]->getPlainPassword('user2pass');

        $this->questions[1] = new Question();
        $this->questions[1]->setId(1);
        $this->questions[1]->setText('How long does it take Norbert to get to school?');
        $this->questions[1]->setHints([
            'a' => self::QUESTION_1_PROPER_ANSWER,
            'b' => '10 minutes.',
            'c' => '30 minutes.'
        ]);
        $this->questions[1]->setProperAnswer(self::QUESTION_1_PROPER_ANSWER);
        $this->questions[1]->setTags([new Tag(TagEnum::POLAND)]);

        $this->questions[2] = new Question();
        $this->questions[2]->setId(2);
        $this->questions[2]->setText('How cool is this game?');
        $this->questions[2]->setHints([
            'a' => 'Not really.',
            'b' => 'Quite nice',
            'c' => 'Awesome!'
        ]);
        $this->questions[2]->setProperAnswer('Awesome!');
        $this->questions[1]->setTags([new Tag(TagEnum::MATH)]);

        $this->games[1] = new Game();
        $this->games[1]->setRoundQuestionCountLimit(2);
        $this->games[1]->setId(1);
        $this->games[1]->addUser($this->users[1]);
        $this->games[1]->addUser($this->users[2]);
        $this->games[1]->setCurrentQuestion($this->questions[1]);
    }

    public function testOnUserAddedScoresForHimAreCreatedAsZero()
    {
        $game = $this->games[1];
        $user3 = new User();
        $user3->setId(3);
        $user3->setUsername('user3');
        $user3->setEmail('user3@foo.bar');
        $user3->getPlainPassword('user3pass');
        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());

        $gameService->onUserAdded($game, $user3);
        $this->assertCount(3, $game->getCurrentScores());
        $this->assertEquals(0, $game->getUserCurrentScore($user3));
    }

    public function testOnUserAddedAnswerForHimIsSetToNull()
    {
        $game = $this->games[1];
        $user3 = new User();
        $user3->setId(3);
        $user3->setUsername('user3');
        $user3->setEmail('user3@foo.bar');
        $user3->getPlainPassword('user3pass');
        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());

        $gameService->onUserAdded($game, $user3);
        $this->assertCount(3, $game->getCurrentAnswers());
        $this->assertEquals(null, $game->getUserCurrentAnswer($user3));
    }

    public function testOnUserDisconnect()
    {
        $game = $this->games[1];
        $user2 = $this->users[2];
        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());

        $gameService->onUserDisconnect($game, $user2);
        $this->assertCount(1, $game->getCurrentScores());
        $this->assertFalse($game->hasUser($user2));
    }

    /**
     * @depends testOnUserDisconnect
     * @depends testPlayerCanAnswerCurrentQuestion
     */
    public function testOnUserDisconnectSwitchesToNextQuestion()
    {
        $game = $this->games[1];
        $user1 = $this->users[1];
        $user2 = $this->users[2];
        $previousQuestionId = $game->getCurrentQuestion()->getId();

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());

        // first player answers
        $gameService->onUserAnswers($game, $user1, 1, self::QUESTION_1_PROPER_ANSWER);
        // second player disconnects
        $gameService->onUserDisconnect($game, $user2);

        $currentQuestionId = $game->getCurrentQuestion()->getId();

        $this->assertNotEquals(
            $previousQuestionId,
            $currentQuestionId,
            'When user was the only one who has not answered yet and he leaves the game the Game should move to the next question'
        );
    }

    /**
     * @depends testOnUserDisconnect
     */
    public function testOnUserDisconnectFinishesGameIfOwnerLeftGame()
    {
        $game = $this->games[1];
        $user1 = $this->users[1];
        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());

        $gameService->onUserDisconnect($game, $user1);
        $this->assertTrue($game->isFinished(), 'When Game creator leaves Game it should be marked as finished');
    }

    public function testPlayerCanAnswerCurrentQuestion()
    {
        $game = $this->games[1];
        $user = $this->users[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $user, 1, self::QUESTION_1_PROPER_ANSWER);

        $this->assertEquals(self::QUESTION_1_PROPER_ANSWER, $game->getUserCurrentAnswer($user), 'Player should be able to provide an answer');
    }

    /**
     * @depends testPlayerCanAnswerCurrentQuestion
     */
    public function testPlayerCanAnswerOnlyCurrentQuestion()
    {
        $game = $this->games[1];
        $user = $this->users[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $user, $notTheCurrentQuestion = 12, self::QUESTION_1_PROPER_ANSWER);

        $this->assertNull($game->getUserCurrentAnswer($user), 'Player should NOT be able to provide an answer for not current Question');
    }

    /**
     * @depends testPlayerCanAnswerCurrentQuestion
     */
    public function testPlayerCanChangeHisAnswer()
    {
        $game = $this->games[1];
        $user = $this->users[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $user, 1, self::QUESTION_1_PROPER_ANSWER);
        $gameService->onUserAnswers($game, $user, 1, 'Second answer');

        $this->assertEquals('Second answer', $game->getUserCurrentAnswer($user), 'Player should be able to change his answer');
    }

    public function testNextQuestionIsSetWhenAllPlayersAnsweredCurrentQuestion()
    {
        $user1 = $this->users[1];
        $user2 = $this->users[2];

        $game = $this->games[1];
        $questionBeforeAnswers = $game->getCurrentQuestion();

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $user1, 1, self::QUESTION_1_PROPER_ANSWER);
        $gameService->onUserAnswers($game, $user2, 1, '10 minutes.');

        $questionAfterAnswers = $game->getCurrentQuestion();
        $this->assertNotEquals(
            $questionAfterAnswers->getId(),
            $questionBeforeAnswers->getId(),
            'Next question should be used when all players answered.'
        );
    }

    public function testCurrentAnswersAreClearedWhenNewQuestionIsSet()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $this->users[1], 1, self::QUESTION_1_PROPER_ANSWER);
        $gameService->onUserAnswers($game, $this->users[2], 1, '10 minutes.');

        $this->assertCount(2, $game->getCurrentAnswers());
        $this->assertNull($game->getCurrentAnswers()[1]);
        $this->assertNull($game->getCurrentAnswers()[2]);
    }


    public function testPlayerScoreDoesNotIncreasesUntilAllPlayersAnswer()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $this->users[1], 1, self::QUESTION_1_PROPER_ANSWER);

        $this->assertEquals(0, $game->getCurrentScores()[1], 'Player\'s score should not increase yet');
    }

    /**
     * @depends testPlayerScoreDoesNotIncreasesUntilAllPlayersAnswer
     */
    public function testPlayerScoreIncreasesWhenPlayersAnswered()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $this->users[1], 1, self::WRONG_ANSWER);
        $gameService->onUserAnswers($game, $this->users[2], 1, self::QUESTION_1_PROPER_ANSWER);

        $this->assertEquals(0, $game->getCurrentScores()[1], "Player's score should not increase when he's answer is wrong.");
        $this->assertNotEquals(0, $game->getCurrentScores()[2], "Player's score should increase when he's answer is correct.");
    }

    /**
     * @depends testPlayerScoreDoesNotIncreasesUntilAllPlayersAnswer
     * @dataProvider dataProviderUserScores
     */
    public function testPlayerScoreIncreasesDependingOnHowFastHeAnswered($user1Answer, $expectedUser1Score, $user2Answer, $expectedUser2Score)
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $this->users[1], 1, $user1Answer);
        $gameService->onUserAnswers($game, $this->users[2], 1, $user2Answer);

        $this->assertEquals($expectedUser1Score, $game->getCurrentScores()[1], "Player's 1 score should increase properly.");
        $this->assertEquals($expectedUser2Score, $game->getCurrentScores()[2], "Player's 2 score should increase properly");
    }

    /**
     * @return array
     */
    public function dataProviderUserScores()
    {
        // self::QUESTION_1_PROPER_ANSWER
        return [
            // both answers wrong
            [self::WRONG_ANSWER, 0, self::WRONG_ANSWER, 0],
            // only the first user answers correct
            [self::QUESTION_1_PROPER_ANSWER, 2, self::WRONG_ANSWER, 0],
            // only the second user answers correct
            [self::WRONG_ANSWER, 0, self::QUESTION_1_PROPER_ANSWER, 2],
            // both answers correct, player 1 answers faster
            [self::QUESTION_1_PROPER_ANSWER, 2, self::QUESTION_1_PROPER_ANSWER, 1],
        ];
    }

    public function testOnNewGamePicksFirstQuestion()
    {
        $game = $this->games[1];
        $game->setCurrentQuestion(null);

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onNewGame($game);

        $this->assertNotNull($game->getCurrentQuestion());
    }

    /**
     * @depends testPlayerScoreDoesNotIncreasesUntilAllPlayersAnswer
     */
    public function testOnGameResetClearsCurrentAnswers()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $this->users[1], 1, self::QUESTION_1_PROPER_ANSWER);

        $gameService->onGameReset($game);
        $this->assertEquals(null, $game->getCurrentAnswers()[$userId = 1]);
        $this->assertEquals(null, $game->getCurrentAnswers()[$userId = 2]);
    }

    /**
     * @depends testPlayerScoreDoesNotIncreasesUntilAllPlayersAnswer
     */
    public function testOnGameResetClearsCurrentScores()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onUserAnswers($game, $this->users[1], 1, self::QUESTION_1_PROPER_ANSWER);

        $gameService->onGameReset($game);
        $this->assertEquals(0, $game->getCurrentScores()[$userId = 1]);
        $this->assertEquals(0, $game->getCurrentScores()[$userId = 2]);
    }

    public function testOnGameNextQuestion()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onGameNextQuestion($game);

        $this->assertNotNull($game->getCurrentQuestion());
    }

    public function testOnGameResetPicksNewQuestion()
    {
        $game = $this->games[1];
        $game->setCurrentQuestion(null);

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onGameReset($game);

        $this->assertNotNull($game->getCurrentQuestion());
    }

    public function testOnGameResetStartsNextRound()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());
        $gameService->onNewGame($game);
        $this->assertEquals(1, $game->getRoundNumber(), 'Newly started Game should have round number 1');

        $gameService->onGameReset($game);
        $this->assertEquals(2, $game->getRoundNumber(), 'Game should have round number 2');
    }

    public function testOnTagsSelected()
    {
        $game = $this->games[1];

        $gameService = new GameService($this->createDefaultQuestionRepositoryMock());

        $tags = [
            $this->tags[0], // Poland
            $this->tags[1], // Cinema
        ];

        $tagIds = [1, 2]; // Poland, Cinema
        $gameService->onTagsSelected($game, $tags);

        $this->assertEquals($tagIds, Repo::getIds($game->getTags()));

        return $gameService;
    }

    /**
     * @depends testOnTagsSelected
     */
    public function testQuestionsFromSelectedTagsAreIncludedAndExcluded(GameService $gameService)
    {
        // Game should show questions about Sport and Music
        $this->markTestIncomplete('Write proper assertions etc.');
    }

    /**
     * @return \Paq\GameBundle\Entity\QuestionRepository
     */
    private function createDefaultQuestionRepositoryMock()
    {
        $questionRepository = $this->getMock('\Paq\GameBundle\Entity\QuestionRepository', [], [], '', false);
        $questionRepository
            ->expects($this->any())
            ->method('getRandomQuestion')
            ->will($this->returnValue($this->questions[2]));

        return $questionRepository;
    }
}
 
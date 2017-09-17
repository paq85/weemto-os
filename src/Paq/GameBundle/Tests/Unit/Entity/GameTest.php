<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Entity;


use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Entity\QuestionHint;
use Paq\GameBundle\Entity\User;

class GameTest extends \PHPUnit_Framework_TestCase
{

    public function testGetQuestionsLogIsEmptyForNewGame()
    {
        $game = new Game();

        $this->assertEmpty($game->getQuestionsLog());
    }

    public function testGetQuestionsLog()
    {
        $game = new Game();
        $q11 = new Question();
        $q11->setId(11);

        $q32 = new Question();
        $q32->setId(32);

        $game->setCurrentQuestion($q11);

        $this->assertCount(1, $game->getQuestionsLog(), 'There should be log about two Questions');
        $this->assertContains(11, $game->getQuestionsLog(), 'There should be log about Question ID = 11');

        $game->setCurrentQuestion($q32);

        $this->assertCount(2, $game->getQuestionsLog(), 'There should be log about two Questions');
        $this->assertContains(32, $game->getQuestionsLog(), 'There should be log about Question ID = 32');
    }

    public function testGameIsFinished()
    {
        $game = new Game();
        $game->setRoundQuestionCountLimit(2);

        $q11 = new Question();
        $q11->setId(11);

        $q32 = new Question();
        $q32->setId(32);

        $user = new User();
        $user->setId(1);

        $game->addUser($user);

        $game->startNextRound();
        $this->assertFalse($game->isFinished(), 'Game should not be finished until question limit for this round is exceeded');
        $game->setCurrentQuestion($q11);
        $this->assertFalse($game->isFinished(), 'Game should not be finished until question limit for this round is exceeded');
        $game->setCurrentQuestion($q32);
        $this->assertFalse($game->isFinished(), 'Game should not be finished until question limit for this round is exceeded and all Users answered current Question');
        $game->setUserCurrentAnswer($user, 'my answer');
        $this->assertTrue($game->isFinished(), 'Game should be finished when question limit for this round is exceeded and all Users answered current Question');
    }

    public function testStartNextRound()
    {
        $game = new Game();

        $this->assertEquals(0, $game->getRoundNumber(), 'Not started Game should be in round 0');
        $this->assertEquals(0, $game->getRoundQuestionNumber(), 'Not started Game should be in round question 0');

        $game->startNextRound();
        $this->assertEquals(1, $game->getRoundNumber(), 'Just started Game should be in round 1');
        $this->assertEquals(0, $game->getRoundQuestionNumber(), 'Just started Game should be in round question 0');

        $question = new Question();
        $game->setCurrentQuestion($question);
        $this->assertEquals(1, $game->getRoundNumber(), 'Just started Game should be in round 1');
        $this->assertEquals(1, $game->getRoundQuestionNumber(), 'Just started Game with first Question set should be in round question 1');
    }

    public function testSetUserCurrentAnswer()
    {
        $game = new Game();
        $game->setRoundQuestionCountLimit(2);

        $q11 = new Question();
        $q11->setId(11);

        $user = new User();
        $user->setId(1);

        $game->addUser($user);
        $game->setCurrentQuestion($q11);
        $game->startNextRound();

        $game->setUserCurrentAnswer($user, null);
        $this->assertNull($game->getUserCurrentAnswer($user), "User's answer should be NULL");

        $game->setUserCurrentAnswer($user, 'my answer');
        $this->assertEquals('my answer', $game->getUserCurrentAnswer($user), "User's answer should have been set");
    }

    public function testGetQuestionHintsOrder()
    {
        $hint1 = new QuestionHint(null, 'one');
        $hint1->setId(1);
        $hint2 = new QuestionHint(null, 'two');
        $hint2->setId(2);
        $hint3 = new QuestionHint(null, 'three');
        $hint3->setId(3);

        $question = new Question();
        $question->setHints([$hint1, $hint2, $hint3]);
        $game = new Game();
        $game->setCurrentQuestion($question);

        $this->assertCount(3, $game->getCurrentQuestionHintsOrder(), 'Question Hints Order should contain all hints');
    }
}

<?php

namespace Paq\GameBundle\Tests\Controller;

use Paq\GameBundle\DataFixtures\Test\FixturesEnum;
use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class GameServiceControllerTest extends IntegrationTestCase
{

    public function testGamesCreatedReturnsGame()
    {
        $this->logIn('damian');
        $user = $this->refRepo->getReference('user-damian');
        $router = $this->getRouter();
        $route = $router->generate('paq_game_service_games_created', ['user' => $user->getId()]);

        $crawler = $this->client->request('GET', $route);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent, $responseContent);
        $this->assertContains('"game"', $responseContent);
        $responseData = json_decode($responseContent);
        $this->assertCount(1, $responseData->gui->users, 'Game should only have one player');
        $this->assertEquals($user->getId(), $responseData->gui->users[0]->id, 'Game should have the user who created it');
    }

    public function testGamesCreatedReturnsErrorMessageWhenWrongUserProvided()
    {
        $router = $this->getRouter();
        $route = $router->generate('paq_game_service_games_created', ['user' => 'wrongUserId']);

        $crawler = $this->client->request('GET', $route);
        $this->assertContains('error', $this->client->getResponse()->getContent(), $this->client->getResponse()->getContent());
    }

    public function testGameDelete()
    {
        $this->logIn('damian');

        $game = $this->refRepo->getReference('game-first-by-damian-with-agata');
        $router = $this->getRouter();
        $route = $router->generate('paq_game_service_game_delete', ['game' => $game->getId()]);

        $crawler = $this->client->request('GET', $route);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGameJoin()
    {
        $this->logIn('zbyszek');
        $user = $this->refRepo->getReference('user-zbyszek');
        $game = $this->refRepo->getReference('game-second-by-agata-alone');
        $router = $this->getRouter();
        $route = $router->generate('paq_game_service_game_join', ['user_id' => $user->getId(), 'game_id' => $game->getId()]);

        $crawler = $this->client->request('GET', $route);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $actual = json_decode($responseContent);
        // See: doc/gameservice/game_create:response.json
        $this->assertContains('"game"', $responseContent);
    }

    public function testGameReset()
    {
        $this->logIn('damian');

        $userDamian = $this->refRepo->getReference('user-damian');
        $userAgata = $this->refRepo->getReference('user-agata');
        $game = $this->refRepo->getReference('game-first-by-damian-with-agata');
        $router = $this->getRouter();
        $route = $router->generate('paq_game_service_game_reset', ['game' => $game->getId()]);

        $crawler = $this->client->request('POST', $route);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), 'Response should have status code: 200' . PHP_EOL . $responseContent);
        // See: doc/gameservice/game_get:response.json
        /* @var Game $gameDeserialized */
        $gameDeserialized = $this->getSerializer()->deserialize($responseContent, 'Paq\GameBundle\Entity\Game', 'json');
        $this->assertEmpty($gameDeserialized->getCurrentAnswers(), 'Restarted game should not have current answers');
        foreach ($gameDeserialized->getCurrentScores() as $score) {
            $this->assertEquals(0, $score, 'Restarted game should have only scores = 0');
        }
    }

    public function testGameNextQuestion()
    {
        $this->logIn('damian');

        $game = $this->getGame('first-by-damian-with-agata');
        $previousQuestionId = $game->getCurrentQuestion()->getId();
        $route = $this->getRouter()->generate('paq_game_service_game_nextquestion', ['game' => $game->getId()]);

        $this->client->request('POST', $route);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), 'Response should have status code: 200' . PHP_EOL . $responseContent);
        $updatedGame = $this->getSerializer()->deserialize($responseContent, 'Paq\GameBundle\Entity\Game', 'json');
        $this->assertNotEquals($previousQuestionId, $updatedGame->getCurrentQuestion()->getId(), 'Game should have next Question as requested');
    }

    public function testUserRegister()
    {
        $username = 'Jasiu';
        $email = 'jasiu@foo.bar';
        $password = 'jasiuPasiu';

        $router = $this->getRouter();
        $route = $router->generate('paq_game_service_user_register');

        $crawler = $this->client->request(
            'POST',
            $route,
            ['username' => $username, 'email' => $email, 'password' => $password]
        );
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), 'Response should have status code: 200' . PHP_EOL . $responseContent);
        // See: doc/gameservice/game_get:response.json
        $this->assertContains('"id":', $responseContent, 'User ID should be returned');
        $this->assertContains('"username":"' . $username . '"', $responseContent, 'User name should be returned');
    }

    public function testGameUserAnswerAction()
    {
        $this->logIn('agata');

        $expectedAnswer = 'answerfooo';
        $gameReference = $this->refRepo->getReference('game-first-by-damian-with-agata');
        $currentQuestion = $this->refRepo->getReference('question-first');

        $route = $this->getRouter()->generate(
            'paq_game_service_game_user_answer',
            [ 'gameId' => $gameReference->getId(), 'questionId' => $currentQuestion->getId(), 'answer' => $expectedAnswer]
        );
        $crawler = $this->client->request('POST', $route);

        $this->assertJson($this->client->getResponse()->getContent(), 'GameUserAnswer should return JSON; Got: ' . $this->client->getResponse()->getContent());
        /* @var Game $game */
        $game = $this->getSerializer()->deserialize($this->client->getResponse()->getContent(), 'Paq\GameBundle\Entity\Game', 'json');
        $this->assertContains($expectedAnswer, $game->getCurrentAnswers(), 'Answer should have been save in Game details');
    }

    /**
     * @depends testGameUserAnswerAction
     */
    public function testGameUserAnswerActionWhenNoNextQuestionIsAvailable()
    {
        $this->logIn('agata');

        $expectedAnswer = 'answerfooo';
        /* @var Game $gameReference */
        $gameReference = $this->refRepo->getReference('game-first-by-damian-with-agata');
        $currentQuestion = $this->refRepo->getReference('question-first');

        $gameReference->setTags([$this->refRepo->getReference('tag-' . TagEnum::BRAND)]);
        $this->em->persist($gameReference);
        $this->em->flush();

        $route = $this->getRouter()->generate(
            'paq_game_service_game_user_answer',
            [ 'gameId' => $gameReference->getId(), 'questionId' => $currentQuestion->getId(), 'answer' => $expectedAnswer]
        );
        $crawler = $this->client->request('POST', $route);

        $this->logIn('damian');
        $route = $this->getRouter()->generate(
            'paq_game_service_game_user_answer',
            [ 'gameId' => $gameReference->getId(), 'questionId' => $currentQuestion->getId(), 'answer' => $expectedAnswer]
        );
        $crawler = $this->client->request('POST', $route);

        /* @var Game $game */
        $game = $this->getSerializer()->deserialize($this->client->getResponse()->getContent(), 'Paq\GameBundle\Entity\Game', 'json');
        $this->assertNull($game->getCurrentQuestion(), 'Game should not have current Question if there are no more Questions from selected Tags');
    }

    public function testGameSetUserAnswerByNumberAction()
    {
        // NOTICE: This could pass randomly even if there's a bug as hint order is randomized
        $this->logIn('agata');
        $gameReference = $this->getGame('first-by-damian-with-agata');

        $answerIndex = 1;
        $expectedHintId = $gameReference->getCurrentQuestionHintsOrder()[$answerIndex];
        $expectedAnswerHint = null;
        foreach ($gameReference->getCurrentQuestion()->getHints() as $hint) {
            if ($hint->getId() === $expectedHintId) {
                $expectedAnswerHint = $hint;
            }
        }
        $expectedAnswer = $expectedAnswerHint->getText();

        $route = $this->getRouter()->generate(
            'paq_game_service_game_setuseranswerbyindex',
            [ 'gameId' => $gameReference->getId(), 'answer_index' => $answerIndex]
        );
        $crawler = $this->client->request('POST', $route);

        $this->assertJson($this->client->getResponse()->getContent(), 'GameUserAnswer should return JSON; Got: ' . $this->client->getResponse()->getContent());
        /* @var Game $game */
        $game = $this->getSerializer()->deserialize($this->client->getResponse()->getContent(), 'Paq\GameBundle\Entity\Game', 'json');
        $this->assertContains($expectedAnswer, $game->getCurrentAnswers(), 'Answer should have been saved in Game details');
    }

    /**
     * Simulate two users playing three rounds 3 questions each (round limit reached)
     * @depends testGameSetUserAnswerByNumberAction
     * @bug WEEM-51
     */
    public function testGameEndsWhenMaxNumberOfRoundQuestionsHaveBeenAnsweredAction()
    {
        $questionsLimit = $this->client->getContainer()->getParameter('paq_game.game_round_question_count_limit');

        $damian = static::createClient();
        $this->logIn('damian', null, $damian);

        $agata = static::createClient();
        $this->logIn('agata', null, $agata);

        $gameReference = $this->getGame('first-by-damian-with-agata');

        for ($iRound = 1; $iRound <= 3; ++$iRound) {
            for ($iQuestion = 0; $iQuestion < $questionsLimit; ++$iQuestion) {
                $route = $this->getRouter($agata)->generate(
                    'paq_game_service_game_setuseranswerbyindex',
                    [ 'gameId' => $gameReference->getId(), 'answer_index' => 1]
                );
                $agata->request('POST', $route);
                $this->assertEquals(Response::HTTP_OK, $agata->getResponse()->getStatusCode());
                $this->assertJson($agata->getResponse()->getContent(), 'GameUserAnswer should return JSON; Got: ' . $agata->getResponse()->getContent());
                $damian->request('POST', $route);
                $this->assertEquals(Response::HTTP_OK, $damian->getResponse()->getStatusCode());
                $this->assertJson($damian->getResponse()->getContent(), 'GameUserAnswer should return JSON; Got: ' . $damian->getResponse()->getContent());
            }

            // next round
            $route = $this->getRouter($agata)->generate('paq_game_service_game_reset', ['game' => $gameReference->getId()]);
            $crawler = $damian->request('POST', $route);
        }

        /* @var Game $game */
        $game = $this->getSerializer($agata)->deserialize($agata->getResponse()->getContent(), 'Paq\GameBundle\Entity\Game', 'json');
        $this->assertTrue($game->isFinished(), 'Game should be finished once Round Question Limit has been reached');
    }

    public function testGameSetUserAnswerByNumberActionReturns403IfGameIsFinished()
    {
        $game = $this->getGame('first-by-damian-with-agata');
        $this->userDisconnectsGame($this->getUser('damian'), $game);

        $this->logIn('agata');
        $route = $this->getRouter()->generate(
            'paq_game_service_game_setuseranswerbyindex',
            [ 'gameId' => $game->getId(), 'answer_index' => 1]
        );
        $crawler = $this->client->request('POST', $route);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode(), 'User should be forbidden from answering finished Game');
    }

    /**
     * @depends testGameJoin
     */
    public function testUserDisconnect()
    {
        $userAgata = $this->getUser('agata');
        $game = $this->getGame('first-by-damian-with-agata');
        $response = $this->userDisconnectsGame($userAgata, $game);

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), 'Response should have status code: 200' . PHP_EOL . $responseContent);

        /* @var Game $game */
        $game = $this->getSerializer()->deserialize($response->getContent(), 'Paq\GameBundle\Entity\Game', 'json');
        $this->assertFalse($game->hasUser($userAgata));
    }

    /**
     * @depends testUserDisconnect
     */
    public function testUserLogoutRunsUserDisconnect()
    {
        $this->logIn('agata');
        $game = $this->getGame('first-by-damian-with-agata');

        $this->logOut();
        $this->assertCount(1, $game->getUsers(), 'Game should only have less users once User logs out');
    }

    public function testGameSetTagsAction()
    {
        $this->logIn('damian');

        $gameReference = $this->refRepo->getReference('game-first-by-damian-with-agata');

        $expectedTags = [
            1, 2, 3
        ];
        $route = $this->getRouter()->generate(
            'paq_game_service_game_set_tags',
            [ 'gameId' => $gameReference->getId(), 'tagIds' => $expectedTags]
        );
        $crawler = $this->client->request('POST', $route);

        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode(),
            'User should be able to pick Tags used in Game ' . PHP_EOL . $this->client->getResponse()->getContent()
        );
    }

}

<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Controller;

use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Tests\Integration\IntegrationTestCase;

class GuiControllerTest extends IntegrationTestCase
{

    public function testUserCheck()
    {
        $route = $this->getRouter()->generate('paqgame_gui_user_check');
        $crawler = $this->client->request('GET', $route);

        $this->assertContains('Wpisz imię', $this->client->getResponse()->getContent());

        $form = $crawler->selectButton('submit')->form();

        $form['display_name'] = 'Dawid';

        $crawler = $this->client->submit($form);

        $response = $this->client->getResponse();
        $this->assertRedirectToRoute('paqgame_gui_start', $response, 'Once registered user should get to the homepage');

        $this->assertUserExists(['displayName' => 'Dawid']);
    }

    public function testUserCheckNotForAuthenticatedUser()
    {
        $this->logIn('damian');

        $route = $this->getRouter()->generate('paqgame_gui_user_check');
        $crawler = $this->client->request('GET', $route);

        $response = $this->client->getResponse();
        $this->assertRedirectToRoute('paqgame_gui_start', $response, 'Already authenticated User should get to the homepage');
    }

    public function testUserCheckRedirectsToRequestedPage()
    {
        $this->logIn('damian');

        $route = $this->getRouter()->generate('paqgame_gui_user_check', ['tr' => 'paq_game_gui_page_help', 'page' => 'main']);
        $crawler = $this->client->request('GET', $route);

        $response = $this->client->getResponse();
        $this->assertRedirectToRoute('paq_game_gui_page_help', $response, 'Already authenticated User should get to the homepage');
    }

    public function testUserCheckRedirectsToRequestedPageIncludingGETParameters()
    {
        $route = $this->getRouter()->generate('paqgame_gui_user_check', ['tr' => 'paq_game_gui_page_help', 'page' => 'main', 'foo' => 'foo1', 'bar' => 3233]);
        $crawler = $this->client->request('GET', $route);

        $form = $crawler->selectButton('submit')->form();
        $form['display_name'] = 'Dawid';
        $crawler = $this->client->submit($form);

        $response = $this->client->getResponse();
        $this->assertRedirectToRoute('paq_game_gui_page_help', $response, 'Already authenticated User should get to the homepage');
        $this->assertContains('foo=foo1', $response->headers->get('Location'), 'Redirection should have "foo" param');
        $this->assertContains('bar=3233', $response->headers->get('Location'), 'Redirection should have "bar" param');
    }

    public function testUserCheck404WhenUserTriesToJoinNonExistingGame()
    {
        $route = $this->getRouter()->generate('paqgame_gui_user_check', ['tr' => 'p_g_g_j', 'gcode' => 'nonexistinggame']);
        $crawler = $this->client->request('GET', $route);

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode(), 'It should return 404');
    }

    public function testDisconnectGame()
    {
        $this->logIn('agata');
        $game = $this->refRepo->getReference('game-first-by-damian-with-agata');

        $route = $this->getRouter()->generate('paqgame_gui_disconnect_game', ['game' => $game->getId()]);
        $crawler = $this->client->request('GET', $route);

        $this->assertEquals(
            302,
            $this->client->getResponse()->getStatusCode(),
            'User should be able to disconnect and be redirected to main page; ' . $this->client->getResponse()->getContent()
        );
    }

}
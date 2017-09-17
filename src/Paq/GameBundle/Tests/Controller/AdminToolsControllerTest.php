<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Controller;


use Paq\GameBundle\Tests\Integration\IntegrationTestCase;

class AdminToolsControllerTest extends IntegrationTestCase
{

    public function testRemoveObsoleteGames()
    {
        $router = $this->getRouter();
        $route = $router->generate('paqgame_admintools_games_remove_obsolete', ['max_age' => 3600]);

        $this->logIn('admin');
        $crawler = $this->client->request('GET', $route);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent, $responseContent);
        $responseData = \GuzzleHttp\json_decode($responseContent);

        $this->assertEquals(1, $responseData->removedGameCount, 'One obsolete Game should have been removed');
    }

}

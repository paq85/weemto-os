<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Controller;

use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\PaqGameBundle;
use Paq\GameBundle\Tests\Integration\IntegrationTestCase;

class ScreenControllerTest extends IntegrationTestCase
{

    public function testGameCreateAction()
    {
        $this->logIn('zbyszek');

        $route = $this->getRouter()->generate('p_g_g_c');
        $crawler = $this->client->request('GET', $route, ['tags' => [$this->getTag(TagEnum::GEOGRAPHY)->getId()]]);

        $response = $this->client->getResponse();
        $this->assertEquals(302, $response->getStatusCode(), 'Once Game created User should be redirected to board');
        $this->assertContains('/board', $response->headers->get('location'), 'Once Game created User should be redirected to board');
    }

    /**
     * @depends testGameCreateAction
     */
    public function testGameCreateForTags()
    {
        $this->logIn('zbyszek');
        $user = $this->refRepo->getReference('user-zbyszek');
        $route = $this->getRouter()->generate(
            'p_g_g_c',
            [
                'user_id' => $user->getId(),
                'tags' => [
                    $this->getTag(TagEnum::GEOGRAPHY)->getId(),
                    $this->getTag(TagEnum::ANIMALS)->getId()
                ]
            ]
        );

        $crawler = $this->client->request('GET', $route);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), 'Once Game created User should be redirected to board');

        $game = $this->em->getRepository('PaqGameBundle:Game')->findOneByCreatedBy($user);
        $this->assertCount(2, $game->getTags(), 'Game should be created for Tags requested by User');
    }

    /**
     * @depends testGameCreateAction
     */
    public function testGameCreateForEnglishAction()
    {
        $locale = PaqGameBundle::LOCALE_EN;
        $this->logIn('zbyszek');

        // When I create Game for "en" locale
        $route = $this->getRouter()->generate('p_g_g_c', ['_locale' => $locale]);
        $crawler = $this->client->request('GET', $route, ['tags' => [$this->getTag(TagEnum::MATH)->getId()]]);

        // It should be created
        $response = $this->client->getResponse();
        $this->assertEquals(302, $response->getStatusCode(), 'Once Game created User should be redirected to board; ' . $response->getContent());
        // And Game's Question should come from "en" locale
        $user = $this->getUser('zbyszek');

        // Need to use Doctrine initialized for Zbyszek's request - locale
        $games = $this->getDoctrine()->getRepository('PaqGameBundle:Game')->findByUser($user);
        $this->assertCount(1, $games, 'User should have one Game created');
        $game = $games[0];
        $this->assertEquals(
            $locale,
            $game->getCurrentQuestion()->getEnabledLocale(),
            'Created Game Question should come from User requested locale'
        );
    }

}
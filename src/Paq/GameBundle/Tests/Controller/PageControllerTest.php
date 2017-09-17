<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Tests\Controller;

use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class PageControllerTest extends IntegrationTestCase
{
    public function testChallengePage()
    {
        $tagId = $this->getTag(TagEnum::MATH)->getId();
        $crawler = $this->client->request('GET', "/pl/challenges/$tagId/whatever");
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testOldChallengeURLPermanentRedirect()
    {
        $tagName = $this->getTag(TagEnum::MATH)->getName();
        $crawler = $this->client->request('GET', "/pl/challenges/$tagName");
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse()->getStatusCode());
    }

    public function testRanking()
    {
        $crawler = $this->client->request('GET', "/pl/ranking/");
        $content = $this->client->getResponse()->getContent();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $content);
        $this->assertContains('Damian', $content);
    }

    public function testRankingForTag()
    {
        $tagId = $this->getTag(TagEnum::MATH)->getId();
        $crawler = $this->client->request('GET', "/pl/ranking/$tagId");
        $content = $this->client->getResponse()->getContent();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $content);
        $this->assertContains('Damian', $content);
    }
}
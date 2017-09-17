<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit;


use Paq\GameBundle\Entity\Game;
use Paq\GameBundle\Entity\User;

class SerializerTest extends \PHPUnit_Framework_TestCase
{

    public function testEntityGameTest()
    {
        $serializer =
            \JMS\Serializer\SerializerBuilder::create()
                ->addMetadataDir(SRC_DIR . '/Paq/GameBundle/Resources/config/serializer')
                ->setPropertyNamingStrategy(new \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy())
                ->build();

        $user = new User();
        $user->setId(1);

        $game = new Game();
        $game->addUser($user);

        $json = $serializer->serialize($game, 'json');
        $this->assertContains('"isFinished":false', $json, 'JSON should contain info about game not finished');

        $game->removeUser($user);
        $json = $serializer->serialize($game, 'json');
        $this->assertContains('"isFinished":true', $json, 'JSON should contain info about game finished');
    }
}
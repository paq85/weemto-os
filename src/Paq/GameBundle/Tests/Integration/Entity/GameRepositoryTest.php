<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Integration\Entity;


use Paq\GameBundle\Entity\GameRepository;
use Paq\GameBundle\Tests\Integration\IntegrationTestCase;

class GameRepositoryTest extends IntegrationTestCase
{

    public function testDeleteAll()
    {
        $usersBefore = $this->getDoctrine()->getRepository('PaqGameBundle:User')->findAll();

        $this->getRepo()->deleteAll();

        $questions = $this->getRepo()->findAll();
        $this->assertCount(0, $questions, 'There should be no Games in the DB');

        $usersAfter = $this->getDoctrine()->getRepository('PaqGameBundle:User')->findAll();
        $this->assertEquals(count($usersBefore), count($usersAfter), 'Removing Games should not remove Users');
    }

    public function testFindByUser()
    {
        $damian = $this->getUser('damian');
        $games = $this->getRepo()->findByUser($damian);

        $this->assertCount(1, $games, 'One Game should have been found.');
        $this->assertContains($damian, $games[0]->getUsers(), 'User should be in the Game found');
    }

    /**
     * @dataProvider dataProviderTestRemoveCreatedBefore
     */
    public function testRemoveCreatedBefore($time, $expectedRemovedGameCount)
    {
        $date = new \DateTime($time);
        $removedGameCount = $this->getRepo()->removeCreatedBefore($date);

        $this->assertEquals($expectedRemovedGameCount, $removedGameCount, 'Only obsolete Games should be removed');
    }

    /**
     * @return array
     */
    public function dataProviderTestRemoveCreatedBefore()
    {
        return [
            ['-1 hour', 1],
            ['-10 seconds', 1],
            ['-3 hours', 0]
        ];
    }

    public function testGenerateGCode()
    {
        $gcode = $this->getRepo()->generateGCode();
        $this->assertNotNull($gcode, 'GCode should be generated');
    }

    /**
     * @return GameRepository
     */
    private function getRepo()
    {
        return $this->em->getRepository('PaqGameBundle:Game');
    }
}

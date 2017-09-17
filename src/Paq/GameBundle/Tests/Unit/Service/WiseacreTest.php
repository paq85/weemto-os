<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Service;


use Paq\GameBundle\Model\PolishProvince;
use Paq\GameBundle\Service\Wiseacre;

class WiseacreTest extends \PHPUnit_Framework_TestCase
{

    public function testGetMinimum()
    {
        $wiseacre = new Wiseacre();
        $subjects = $this->getSubjects();

        $this->assertEquals(1, $wiseacre->getMinimum($subjects, 'getSize')->getSize());
        $this->assertEquals(10, $wiseacre->getMinimum($subjects, 'getPopulation')->getPopulation());
    }

    public function testGetMaximum()
    {
        $wiseacre = new Wiseacre();
        $subjects = $this->getSubjects();

        $this->assertEquals(13, $wiseacre->getMaximum($subjects, 'getSize')->getSize());
        $this->assertEquals(103, $wiseacre->getMaximum($subjects, 'getPopulation')->getPopulation());
    }

    public function testGetBroadest()
    {
        $wiseacre = new Wiseacre();

        $subjects = $this->getSubjects();

        $this->assertEquals(13, $wiseacre->getBroadest($subjects)->getSize(), 'The broadest subject should have been returned');
    }

    public function testGetSmallest()
    {
        $wiseacre = new Wiseacre();

        $subjects = $this->getSubjects();

        $this->assertEquals(1, $wiseacre->getSmallest($subjects)->getSize(), 'The smallest subject should have been returned');
    }

    public function testGetMostPopulated()
    {
        $wiseacre = new Wiseacre();

        $subjects = $this->getSubjects();

        $this->assertEquals(
            103,
            $wiseacre->getMostPopulated($subjects)->getPopulation(),
            'The most populated subject should have been returned'
        );
    }

    public function testGetLeastPopulated()
    {
        $wiseacre = new Wiseacre();

        $subjects = $this->getSubjects();

        $this->assertEquals(
            10,
            $wiseacre->getLeastPopulated($subjects)->getPopulation(),
            'The most populated subject should have been returned'
        );
    }

    public function testGetMostUrbanized()
    {
        $wiseacre = new Wiseacre();

        $subjects = $this->getSubjects();

        $this->assertEquals(
            203,
            $wiseacre->getMostUrbanized($subjects)->getUrbanization(),
            'The most populated subject should have been returned'
        );
    }

    public function testGetLeastUrbanized()
    {
        $wiseacre = new Wiseacre();

        $subjects = $this->getSubjects();

        $this->assertEquals(
            20,
            $wiseacre->getLeastUrbanized($subjects)->getUrbanization(),
            'The most populated subject should have been returned'
        );
    }

    /**
     * @return PolishProvince[]
     */
    private function getSubjects()
    {
        $subjects = [];

        $subject = new PolishProvince('a');
        $subject->size = 1;
        $subject->population = 10;
        $subject->urbanization = 20;
        $subjects[] = $subject;

        $subject = new PolishProvince('b');
        $subject->size = 11;
        $subject->population = 101;
        $subject->urbanization = 201;
        $subjects[] = $subject;

        $subject = new PolishProvince('c');
        $subject->size = 12;
        $subject->population = 102;
        $subject->urbanization = 202;
        $subjects[] = $subject;

        $subject = new PolishProvince('d');
        $subject->size = 13;
        $subject->population = 103;
        $subject->urbanization = 203;
        $subjects[] = $subject;

        return $subjects;
    }
}
 
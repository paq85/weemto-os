<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Service;


use Paq\GameBundle\Service\Wiki;

class WikiTest extends \PHPUnit_Framework_TestCase
{

    public function testGetPolishProvinces()
    {
        $wiki = new Wiki();

        $provinces = $wiki->getPolishProvinces();

        $this->assertCount(16, $provinces);
        $this->assertEquals('dolnośląskie', $provinces[0]->getName());
        $this->assertEquals('Wrocław', $provinces[0]->getCapital());
        $this->assertEquals(19946.74, $provinces[0]->getSize(), '', 0.01);
        $this->assertEquals(2914362, $provinces[0]->getPopulation());
        $this->assertEquals(69.6, $provinces[0]->getUrbanization(), '', 0.1);
        $this->assertEquals(13.2, $provinces[0]->getUnemployment(), '', 0.1);
        $this->assertEquals(44961, $provinces[0]->getPkbPerPerson());
        $this->assertEquals('D', $provinces[0]->getLicensePlate());
    }

    public function testGetCountries()
    {
        $wiki = new Wiki();

        $countries = $wiki->getCountries();

        $this->assertCount(194, $countries);
        $this->assertEquals('Afganistan', $countries[0]->getName());
        $this->assertEquals('Azja', $countries[0]->getContinent());
        $this->assertEquals(652230, $countries[0]->getSize());
        $this->assertEquals(30419928, $countries[0]->getPopulation());
    }
}
 
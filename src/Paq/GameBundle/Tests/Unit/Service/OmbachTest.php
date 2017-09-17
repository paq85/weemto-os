<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Service;


use Paq\GameBundle\Service\Ombach;

class OmbachTest extends \PHPUnit_Framework_TestCase
{

    public function dataProviderTestRandomNumberBesideZero()
    {
        return [
            [0, 10],
            [10, 0],
            [4, 5],
            [5, 5],
            [0, 0]
        ];
    }

    /**
     * @param int $a
     * @param int $b
     * @dataProvider dataProviderTestRandomNumberBesideZero
     */
    public function testRandomNumberBesideZero($a, $b)
    {
        $ombach = new Ombach();
        $actual = $ombach->randomNumberBesideZero($a, $b);

        $this->assertNotEquals(0, $actual);
    }

    public function testCombinationWithoutRepetitionOfTwoElements()
    {
        $ombach = new Ombach();
        $subjects = ['a', 'b', 'c', 'd'];

        $expectedResult = [
            ['a', 'b'],
            ['a', 'c'],
            ['a', 'd'],
            ['b', 'c'],
            ['b', 'd'],
            ['c', 'd'],
        ];

        $actual = $ombach->combinationWithoutRepetition($subjects, 2);

        $this->assertCount(count($expectedResult), $actual);
        foreach ($expectedResult as $subset) {
            $this->assertContains($subset, $expectedResult);
        }
    }

    public function testCombinationWithoutRepetitionOfThreeElements()
    {
        $ombach = new Ombach();
        $subjects = ['a', 'b', 'c', 'd'];

        $expectedResult = [
            ['a', 'b', 'c'],
            ['a', 'b', 'd'],
            ['a', 'c', 'd'],
            ['b', 'c', 'd']
        ];

        $actual = $ombach->combinationWithoutRepetition($subjects, 3);

        $this->assertCount(count($expectedResult), $actual);
        foreach ($expectedResult as $subset) {
            $this->assertContains($subset, $expectedResult);
        }
    }

    public function testCombinationWithoutRepetitionWithNumbersOfTwoElements()
    {
        $ombach = new Ombach();

        $expectedResult = [
            [0, 1],
            [0, 2],
            [0, 3],
            [1, 2],
            [1, 3],
            [2, 3],
        ];

        $actual = $ombach->combinationWithoutRepetitionWithNumbers(4, 2);

        $this->assertEquals($expectedResult, $actual);
    }

    public function testCombinationWithoutRepetitionWithNumbersOfThreeElements()
    {
        $ombach = new Ombach();

        $expectedResult = [
            [0, 1, 2],
            [0, 1, 3],
            [0, 2, 3],
            [1, 2, 3],
        ];

        $actual = $ombach->combinationWithoutRepetitionWithNumbers(4, 3);

        $this->assertEquals($expectedResult, $actual);
    }

    public function testCombinationWithRepetitionWithNumbersOfTwoElements()
    {
        $ombach = new Ombach();

        $expectedResult = [
            [0, 0],
            [0, 1],
            [0, 2],
            [0, 3],
            [1, 1],
            [1, 2],
            [1, 3],
            [2, 2],
            [2, 3],
            [3, 3],
        ];

        $actual = $ombach->combinationWithRepetitionWithNumbers(4, 2);

        $this->assertEquals($expectedResult, $actual);
    }

    public function testCombinationWithRepetitionOfTwoElements()
    {
        $ombach = new Ombach();
        $subjects = ['a', 'b', 'c', 'd'];

        $expectedResult = [
            ['a', 'a'],
            ['a', 'b'],
            ['a', 'c'],
            ['a', 'd'],
            ['b', 'b'],
            ['b', 'c'],
            ['b', 'd'],
            ['c', 'c'],
            ['c', 'd'],
            ['d', 'd'],
        ];

        $actual = $ombach->combinationWithRepetition($subjects, 2);

        $this->assertCount(count($expectedResult), $actual);
        foreach ($expectedResult as $subset) {
            $this->assertContains($subset, $expectedResult);
        }
    }

    public function dataProviderTestFactorial()
    {
        return [
            [0, 1],
            [1, 1],
            [2, 2],
            [3, 6],
            [4, 24]
        ];
    }

    /**
     * @param int $number
     * @param int $expected
     * @dataProvider dataProviderTestFactorial
     */
    public function testFactorial($number, $expected)
    {
        $ombach = new Ombach();

        $this->assertEquals($expected, $ombach->factorial($number));
    }

    public function dataProviderTestCountCombinationWithoutRepetition()
    {
        return [
            [1, 1, 1],
            [2, 1, 2],
            [3, 2, 3],
            [4, 2, 6],
            [4, 3, 4],
            [16, 3, 560]
        ];
    }

    /**
     * @param int $subjectsCount
     * @param int $elementCount
     * @param int $expected
     * @dataProvider dataProviderTestCountCombinationWithoutRepetition
     */
    public function testCountCombinationWithoutRepetition($subjectsCount, $elementCount, $expected)
    {
        $ombach = new Ombach();

        $this->assertEquals($expected, $ombach->countCombinationWithoutRepetition($subjectsCount, $elementCount));
    }

    public function testCountCombinationWithoutRepetitionForLargeSet()
    {
        if (!extension_loaded('gmp')) {
            $this->markTestSkipped('GMP extension not installed');
        }

        $ombach = new Ombach();

        $this->assertEquals(161700, $ombach->countCombinationWithoutRepetition(100, 3));
        $this->assertEquals(1313400, $ombach->countCombinationWithoutRepetition(200, 3));
    }

    public function dataProviderTestCountCombinationWithRepetition()
    {
        return [
            [1, 1, 1],
            [4, 2, 10],
            [16, 3, 816],
        ];
    }

    /**
     * @param int $n
     * @param int $k
     * @param int $expected
     * @dataProvider dataProviderTestCountCombinationWithRepetition
     */
    public function testCountCombinationWithRepetition($n, $k, $expected)
    {
        $ombach = new Ombach();

        $this->assertEquals($expected, $ombach->countCombinationWithRepetition($n, $k));
    }

}
 
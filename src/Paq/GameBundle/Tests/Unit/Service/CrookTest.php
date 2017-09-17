<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Test\Unit\Service;


use Paq\GameBundle\Service\Crook;

class CrookTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSum()
    {
        $crook = new Crook();
        $answers = $crook->getSum(4, 7, 2);

        $this->assertCount(2, $answers);
        $this->assertNotContains(11, $answers, 'Crook should not provide a proper answer');
        $this->assertNotEquals($answers[0], $answers[1], 'Crook should provide various answers');
    }

    public function dataProviderTestGetDifference()
    {
        return [
            [10, 5, 5],
            [0, 1, -1]
        ];
    }

    /**
     * @param $a
     * @param $b
     * @param $properAnswer
     * @dataProvider dataProviderTestGetDifference
     */
    public function testGetDifference($a, $b, $properAnswer)
    {
        $crook = new Crook();
        $answers = $crook->getDifference($a, $b, 2);

        $this->assertCount(2, $answers);
        $this->assertNotContains($properAnswer, $answers, 'Crook should not provide a proper answer');
        $this->assertNotEquals($answers[0], $answers[1], 'Crook should provide various answers');
    }

    public function testGetProduct()
    {
        $crook = new Crook();
        $answers = $crook->getProduct(4, 7, 2);

        $this->assertCount(2, $answers);
        $this->assertNotContains(28, $answers, 'Crook should not provide a proper answer');
        $this->assertNotEquals($answers[0], $answers[1], 'Crook should provide various answers');
    }
}
 
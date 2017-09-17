<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Service;


use Paq\GameBundle\Service\Question\Generator;
use Paq\GameBundle\Service\Question\GeneratorResult;
use Paq\GameBundle\Service\Wiseacre;
use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Tests\Unit\Fixtures;

class GeneratorResultTest extends \PHPUnit_Framework_TestCase
{
    public function testCurrent()
    {
        $wiseacre = new Wiseacre();
        $places = (new Fixtures())->getPolishProvinces();
        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Dolnośląskie');

        $properAnswerFinder = function($places) use ($wiseacre) {
            return $wiseacre->getMaximum($places, 'getSize');
        };

        $generatorResult = new GeneratorResult($places, Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST, $properAnswerFinder);

        $this->assertInstanceOf('\\Paq\\GameBundle\\Entity\\Question', $generatorResult->current());
    }

    public function testIterator()
    {
        $wiseacre = new Wiseacre();
        $places = (new Fixtures())->getPolishProvinces();
        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Dolnośląskie');

        $properAnswerFinder = function($places) use ($wiseacre) {
            return $wiseacre->getMaximum($places, 'getSize');
        };

        $generatorResult = new GeneratorResult($places, Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST, $properAnswerFinder);

        $counter = 0;
        foreach ($generatorResult as $iQuestion) {
            $this->assertInstanceOf('\\Paq\\GameBundle\\Entity\\Question', $iQuestion);
            $counter++;
        }

        $this->assertEquals(4, $counter);
    }

    public function testIteratorOfEmptyCollection()
    {
        $wiseacre = new Wiseacre();
        $places = [];
        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Dolnośląskie');

        $properAnswerFinder = function($places) use ($wiseacre) {
            return $wiseacre->getMaximum($places, 'getSize');
        };

        $generatorResult = new GeneratorResult($places, Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST, $properAnswerFinder);

        $counter = 0;
        foreach ($generatorResult as $iQuestion) {
            $this->assertInstanceOf('\\Paq\\GameBundle\\Entity\\Question', $iQuestion);
            $counter++;
        }

        $this->assertEquals(0, $counter);
    }

    public function testAppendIterator()
    {
        $wiseacre = new Wiseacre();
        $places = (new Fixtures())->getPolishProvinces();

        $properAnswerFinder = function($places) use ($wiseacre) {
            return $wiseacre->getMaximum($places, 'getSize');
        };

        $generatorResult1 = new GeneratorResult($places, Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST, $properAnswerFinder);

        $properAnswerFinder = function($places) use ($wiseacre) {
            return $wiseacre->getMinimum($places, 'getUrbanization');
        };
        $generatorResult2 = new GeneratorResult($places, Generator::QUESTION_TEXT_WHICH_IS_THE_LEAST_URBANIZED, $properAnswerFinder);

        $appendIterator = new \AppendIterator();
        $appendIterator->append($generatorResult1);
        $appendIterator->append($generatorResult2);

        $count = 0;
        foreach ($appendIterator as $element) {
            ++$count;
        }
        $this->assertEquals(8, $count, 'AppendIterator should return 8 questions');
    }

} 
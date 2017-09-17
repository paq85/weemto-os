<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Service;


use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Model\PolishProvince;
use Paq\GameBundle\Service\Ombach;
use Paq\GameBundle\Service\Question\Generator;
use Paq\GameBundle\Service\Wiseacre;
use Paq\GameBundle\Tests\Unit\Fixtures;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testGenerateCombinationWithoutRepetition()
    {
        $places = (new Fixtures())->getPolishProvinces();

        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_IS_THE_MOST_URBANIZED);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Dolnośląskie');

        $wiseacre = new Wiseacre();
        $generator = new Generator();
        $generatedQuestions = $generator->generateCombinationWithoutRepetition(
            Generator::QUESTION_TEXT_WHICH_IS_THE_MOST_URBANIZED,
            $places,
            function($places) use ($wiseacre) {
                return $wiseacre->getMostUrbanized($places);
            }
        );

        $expectedQuestionCount = (new Ombach())->countCombinationWithoutRepetition(4, 3);
        $this->assertCount($expectedQuestionCount, $generatedQuestions);

        $this->assertHasQuestion($expectedQuestion1, $generatedQuestions);
    }

    public function testGenerateWhichOneIsTheBiggest()
    {
        $places = (new Fixtures())->getPolishProvinces();

        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Dolnośląskie');

        $generator = new Generator();
        $generatedQuestions = $generator->generateWhichOneIsTheBiggest(
            Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST,
            $places
        );

        $expectedQuestionCount = (new Ombach())->countCombinationWithoutRepetition(4, 3); // 4
        $this->assertCount($expectedQuestionCount, $generatedQuestions);

        $this->assertHasQuestion($expectedQuestion1, $generatedQuestions);
    }

    public function testGenerateWhichOneIsTheSmallest()
    {
        $places = (new Fixtures())->getPolishProvinces();

        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_SMALLEST);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Opolskie');

        $generator = new Generator();
        $generatedQuestions = $generator->generateWhichOneIsTheSmallest(
            Generator::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_SMALLEST,
            $places
        );

        $expectedQuestionCount = (new Ombach())->countCombinationWithoutRepetition(4, 3);
        $this->assertCount($expectedQuestionCount, $generatedQuestions);

        $this->assertHasQuestion($expectedQuestion1, $generatedQuestions);
    }

    public function testGenerateWhichOneIsMostPopulated()
    {
        $places = (new Fixtures())->getPolishProvinces();

        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_IS_THE_MOST_POPULATED);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Dolnośląskie');

        $generator = new Generator();
        $generatedQuestions = $generator->generateWhichOneIsMostPopulated(
            Generator::QUESTION_TEXT_WHICH_IS_THE_MOST_POPULATED,
            $places
        );

        $expectedQuestionCount = (new Ombach())->countCombinationWithoutRepetition(4, 3); // 4
        $this->assertCount($expectedQuestionCount, $generatedQuestions);

        $this->assertHasQuestion($expectedQuestion1, $generatedQuestions);
    }

    public function testGenerateWhichOneIsLeastPopulated()
    {
        $places = (new Fixtures())->getPolishProvinces();

        $expectedQuestion1 = new Question();
        $expectedQuestion1->setText(Generator::QUESTION_TEXT_WHICH_IS_THE_LEAST_POPULATED);
        $expectedQuestion1->setHints(['Dolnośląskie', 'Opolskie', 'Pomorskie']);
        $expectedQuestion1->setProperAnswer('Opolskie');

        $generator = new Generator();
        $generatedQuestions = $generator->generateWhichOneIsLeastPopulated(
            Generator::QUESTION_TEXT_WHICH_IS_THE_LEAST_POPULATED,
            $places
        );

        $expectedQuestionCount = (new Ombach())->countCombinationWithoutRepetition(4, 3);
        $this->assertCount($expectedQuestionCount, $generatedQuestions);

        $this->assertHasQuestion($expectedQuestion1, $generatedQuestions);
    }

    public function testGenerateMathSumQuestions()
    {
        $maxInt = 10;
        $generator = new Generator($this->getTagRepoForMath());
        $questions = $generator->generateMathSumQuestions($maxInt);

        $expectedQuestionCount = (new Ombach())->countCombinationWithRepetition($maxInt, 2);
        $this->assertCount($expectedQuestionCount, $questions);

        $this->assertContains('+', $questions[0]->getText());
        $this->assertContains('= ?', $questions[0]->getText());
        $this->assertCount(3, $questions[0]->getHints());
    }

    public function testGenerateMathDifferenceQuestions()
    {
        $maxInt = 10;
        $generator = new Generator($this->getTagRepoForMath());
        $questions = $generator->generateMathDifferenceQuestions($maxInt);

        $expectedQuestionCount = (new Ombach())->countCombinationWithRepetition($maxInt, 2);
        $this->assertCount($expectedQuestionCount, $questions);

        $this->assertContains('-', $questions[0]->getText());
        $this->assertContains('= ?', $questions[0]->getText());
        $this->assertCount(3, $questions[0]->getHints());
    }

    public function testGenerateMathProductQuestions()
    {
        $maxInt = 10;
        $generator = new Generator($this->getTagRepoForMath());
        $questions = $generator->generateMathProductQuestions($maxInt);

        $expectedQuestionCount = (new Ombach())->countCombinationWithRepetition($maxInt, 2);
        $this->assertCount($expectedQuestionCount, $questions);

        $this->assertContains('×', $questions[0]->getText());
        $this->assertContains('= ?', $questions[0]->getText());
        $this->assertCount(3, $questions[0]->getHints());
    }

    public function testGeneratedQuestionIsTagged()
    {
        $maxInt = 2;
        $generator = new Generator($this->getTagRepoForMath());
        $questions = $generator->generateMathProductQuestions($maxInt);
        $this->assertNotEquals(0, count($questions[0]->getTags()));
        $this->assertEquals(TagEnum::MATH, $questions[0]->getTags()[0]->getName());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityRepository
     */
    private function getTagRepoForMath()
    {
        $tagRepo = $this->getMockBuilder('\Paq\GameBundle\Entity\TagRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $tagRepo->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValue(new Tag(TagEnum::MATH)));

        return $tagRepo;
    }

    private function assertHasQuestion(Question $expectedQuestion, $questions)
    {
        foreach ($questions as $generatedQuestion) {
            if ($expectedQuestion->equals($generatedQuestion)) {
                $this->assertTrue($expectedQuestion->equals($generatedQuestion));

                return true;
            }
        }

        $this->fail('Expected question not found in generated questions.');
    }
} 
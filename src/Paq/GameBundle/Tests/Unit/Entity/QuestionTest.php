<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit\Entity;


use Paq\GameBundle\Entity\Question;

class QuestionTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructorEmpty()
    {
        $question = new Question();
        $this->assertEquals(null, $question->getText());
    }

    public function testConstructorWithAllParams()
    {
        $question = new Question([
            'text' => 'text 1',
            'proper_answer' => 'proper',
            'hints' => [
                'wrong 1',
                'proper',
                'wrong 2'
            ]
        ]);

        $this->assertEquals('text 1', $question->getText());
        $this->assertEquals('proper', $question->getProperAnswer());
        $this->assertCount(3, $question->getHints());
    }

    public function equalsDataProvider()
    {
        $qReference = new Question([
            'text' => 'Text ccecwc',
            'proper_answer' => 'Hint A',
            'hints' => [
                'Hint A',
                'Hint B',
                'Hint C'
            ]
        ]);

        $qDifferentHintsOrder = new Question([
            'text' => 'Text ccecwc',
            'proper_answer' => 'Hint A',
            'hints' => [
                'Hint B',
                'Hint C',
                'Hint A'
            ]
        ]);

        $qDifferentHint = new Question([
            'text' => 'Text ccecwc',
            'proper_answer' => 'Hint A',
            'hints' => [
                'Hint A',
                'Hint BBBB',
                'Hint C'
            ]
        ]);

        $qDifferentProperAnswer = new Question([
            'text' => 'Text ccecwc',
            'proper_answer' => 'Hint A',
            'hints' => [
                'Hint A',
                'Hint BBBB',
                'Hint C'
            ]
        ]);

        $qDifferentText = new Question([
            'text' => 'Text different',
            'proper_answer' => 'Hint A',
            'hints' => [
                'Hint A',
                'Hint B',
                'Hint C'
            ]
        ]);

        return [
            [$qReference, $qDifferentHintsOrder, true],
            [$qReference, $qDifferentHint, false],
            [$qReference, $qDifferentProperAnswer, false],
            [$qReference, $qDifferentText, false],
        ];
    }

    /**
     * @param Question $question1
     * @param Question $question2
     * @param bool $isSame
     * @dataProvider equalsDataProvider
     */
    public function testEquals(Question $question1, Question $question2, $isSame)
    {
        $this->assertEquals($isSame, $question1->equals($question2));
    }
}
 
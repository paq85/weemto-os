<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service\Question;


use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Service\Ombach;

class GeneratorResult implements \Iterator
{
    /**
     * @var object[]
     */
    private $subjects;

    /**
     * @var string
     */
    private $questionText;

    /**
     * @var callable
     */
    private $findProperAnswerSubject;

    private $tags;

    /**
     * @var Ombach
     */
    private $ombach;

    /**
     * @var int[]
     */
    private $currentKey;

    /**
     * @var boolean
     */
    private $hasMore;

    /**
     * @var Question
     */
    private $currentValue;

    /**
     * @param $subjects
     * @param $questionText
     * @param callable $findProperAnswerSubject
     * @param Tag[] $tags
     */
    public function __construct($subjects, $questionText, callable $findProperAnswerSubject, $tags = [])
    {
        $this->ombach = new Ombach();
        $this->subjects = $subjects;
        $this->questionText = $questionText;
        $this->findProperAnswerSubject = $findProperAnswerSubject;
        $this->tags = $tags;

        $this->rewind();
        $this->next();
    }

    public function current()
    {
        return $this->currentValue;
    }

    public function key()
    {
        return implode('', $this->currentKey);
    }

    public function next()
    {
        if (count($this->subjects) === 0) {
            $this->hasMore = false;
            $this->currentValue = null;
        } else {
            $this->hasMore = $this->ombach->generateNextCombinationWithoutRepetition(
                $this->currentKey,
                count($this->subjects),
                Generator::HINTS_COUNT
            );
            $this->currentValue = $this->createQuestion(
                $this->questionText,
                $this->currentKey,
                $this->subjects,
                $this->findProperAnswerSubject,
                $this->tags
            );
        }
    }

    public function rewind()
    {
        $this->currentKey = [];
        $this->next();
    }

    public function valid()
    {
        return $this->hasMore;
    }

    /**
     * @param string $questionText
     * @param int[] $rawCombination
     * @param $subjects
     * @param callable $findProperAnswerSubject
     * @return Question
     */
    private function createQuestion($questionText, $rawCombination, $subjects, callable $findProperAnswerSubject, $tags = [])
    {
        $question = new Question();
        $question->setText($questionText);
        $hints = [];
        $questionHintsSubjects = [];
        foreach ($rawCombination as $subjectIndex) {
            $questionHintsSubjects[] = $subjects[$subjectIndex];
            $hints[] = $subjects[$subjectIndex]->getName();
        }
        shuffle($hints);
        $question->setHints($hints);
        $properAnswerSubject = $findProperAnswerSubject($questionHintsSubjects);
        $question->setProperAnswer($properAnswerSubject->getName());
        $question->setTags($tags);

        return $question;
    }

} 
<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service;

/**
 * Provides FALSE answers that could cheat the player
 */
class Crook 
{
    private $maxRandTries = 100;

    /**
     * @var Ombach
     */
    private $ombach;

    public function __construct()
    {
        $this->ombach = new Ombach();
    }

    /**
     * @param int $a
     * @param int $b
     * @param int $answerCount
     * @return int[] false answers
     */
    public function getSum($a, $b, $answerCount)
    {
        return $this->getFalseAnswers($a + $b, $answerCount);
    }

    /**
     * @param int $a
     * @param int $b
     * @param int $answerCount
     * @return int[] false answers
     */
    public function getDifference($a, $b, $answerCount)
    {
        return $this->getFalseAnswers($a - $b, $answerCount);
    }

    /**
     * @param int $a
     * @param int $b
     * @param int $answerCount
     * @return int[] false answers
     */
    public function getProduct($a, $b, $answerCount)
    {
        return $this->getFalseAnswers($a * $b, $answerCount);
    }

    /**
     * @param number $correctResult
     * @param int $answerCount
     * @return number[]
     * @throws \InvalidArgumentException
     */
    private function getFalseAnswers($correctResult, $answerCount)
    {
        $falseAnswers = [];
        $randTries = 0;
        for ($i = 0; $i < $answerCount; ++$i) {
            $iFalseAnswer = null;
            while ($iFalseAnswer === null || in_array($iFalseAnswer, $falseAnswers)) { // make sure answers do not duplicate
                $iFalseAnswer = $correctResult + $this->ombach->randomNumberBesideZero(-$correctResult, $correctResult);
                $randTries++;
                if ($randTries > $this->maxRandTries) {
                    // protect against infinite loop
                    throw new \InvalidArgumentException('Could not generate more fake answers in ' . $this->maxRandTries . ' tries');
                }
            }
            $falseAnswers[] = $iFalseAnswer;
        }

        return $falseAnswers;
    }

}
<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service;


class Ombach 
{

    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public function randomNumberBesideZero($a, $b)
    {
        $min = $a;
        $max = $b;
        if ($a > $b) {
            $min = $b;
            $max = $a;
        } elseif ($a === 0 && $b === 0) {
            $min = -3;
            $max = 3;
        }

        $rand = 0;
        while (0 === $rand) {
            $rand = rand($min, $max);
        }

        return $rand;
    }

    /**
     * ( n )
     * ( k )
     *
     * http://en.wikipedia.org/wiki/Combination
     *
     * @param int $subjectsCount "n"
     * @param int $elementCount "k"
     * @return int
     */
    public function countCombinationWithoutRepetition($subjectsCount, $elementCount)
    {
        if (extension_loaded('gmp')) {
            return gmp_intval(gmp_div(
                gmp_fact($subjectsCount),
                gmp_mul(
                    gmp_fact($elementCount),
                    gmp_fact($subjectsCount- $elementCount)
                )
            ));
        } else {
            if ($subjectsCount > 50) {
                throw new \RuntimeException('To work with large numbers please install GMP extension.');
            }

            return ($this->factorial($subjectsCount)) / ($this->factorial($elementCount) * $this->factorial($subjectsCount - $elementCount));
        }

    }

    /**
     * ( k + n  -1 )
     * (     k     )
     *
     * http://en.wikipedia.org/wiki/Combination
     *
     * @param int $n "n"
     * @param int $k "k"
     * @return int
     */
    public function countCombinationWithRepetition($n, $k)
    {
        if (extension_loaded('gmp')) {
            return gmp_intval(gmp_div(
                gmp_fact($k + $n - 1),
                gmp_mul(
                    gmp_fact($k),
                    gmp_fact($n - 1)
                )
            ));
        } else {
            if ($n > 50) {
                throw new \RuntimeException('To work with large numbers please install GMP extension.');
            }

            return ($this->factorial($k + $n - 1)) / ($this->factorial($k) * $this->factorial($n - 1));
        }

    }

    /**
     * @param int $number
     * @return int|resource Calculated $n!
     */
    public function factorial($number)
    {
        if (extension_loaded('gmp')) {
            return gmp_intval(gmp_fact($number));
        } else {
            if ($number > 50) {
                throw new \RuntimeException('To work with large numbers please install GMP extension.');
            }

            $factorial = 1;
            for ($i = $number; $i > 1; --$i) {
                $factorial *= $i;
            }

            return $factorial;
        }
    }

    /**
     * @param array $subjects
     * @param int $elementCount
     * @return array
     */
    public function combinationWithoutRepetition($subjects, $elementCount)
    {
        $combinations = [];
        $aItemsCount = count($subjects);

        $perms = $this->combinationWithoutRepetitionWithNumbers($aItemsCount, $elementCount);

        for ($i = 0, $icount = count($perms); $i < $icount; ++$i) {
            for ($j = 0, $jcount = count($perms[$i]); $j < $jcount; ++$j) {
                $combinations[$i][$j] = $subjects[$perms[$i][$j]];
            }
        }

        return $combinations;
    }

    /**
     * @param int $setCount
     * @param int $iPermutationSize
     * @param null $aPreviousPermutation
     * @return array|bool|null
     */
    public function combinationWithoutRepetitionWithNumbers($setCount, $iPermutationSize)
    {
        $vector = [];
        $combinations = [];
        $canGenerateMore = $this->generateNextCombinationWithoutRepetition($vector, $setCount, $iPermutationSize);
        $combinations[] = $vector;

        while ($canGenerateMore === true) {
            for ($x = 0; $x < $iPermutationSize; ++$x) {
                $canGenerateMore = $this->generateNextCombinationWithoutRepetition($vector, $setCount, $iPermutationSize);
                if ($canGenerateMore === true) {
                    $combinations[] = $vector;
                }
            }
        }

        return $combinations;
    }

    /**
     * @param array $vector
     * @param int $n
     * @param int $k
     * @return bool will next generateNextCombinationWithoutRepetition on given vector provide next combination?
     */
    public function generateNextCombinationWithoutRepetition(&$vector, $n, $k)
    {
        if ($k > $n) {
            throw new \InvalidArgumentException("K [$k] can not be greater than N [$n]");
        }

        if (count($vector) === 0) {
            //initialize: vector[0, ..., k - 1] are 0, ..., k - 1
            for ($j = 0; $j < $k; $j++) {
                $vector[$j] = $j;
            }

            return true;
        }

        //easy case, increase rightmost element
        if ($vector[$k - 1] < $n - 1) {
            $vector[$k - 1]++;
            return true;
        }

        //find rightmost element to increase
        for ($j = $k - 2; $j >= 0; $j--) {
            if ($vector[$j] < $n - $k + $j) {
                break;
            }
        }

        //terminate if vector[0] == n - k
        if ($j < 0) {
            return false;
        }

        //increase
        $vector[$j]++;

        //set right-hand elements
        while ($j < $k - 1) {
            $vector[$j + 1] = $vector[$j] + 1;
            $j++;
        }

        return true;
    }

    /**
     * @param array $subjects
     * @param int $elementCount
     * @return array
     */
    public function combinationWithRepetition($subjects, $elementCount)
    {
        $combinations = [];
        $aItemsCount = count($subjects);

        $perms = $this->combinationWithRepetitionWithNumbers($aItemsCount, $elementCount);

        for ($i = 0, $icount = count($perms); $i < $icount; ++$i) {
            for ($j = 0, $jcount = count($perms[$i]); $j < $jcount; ++$j) {
                $combinations[$i][$j] = $subjects[$perms[$i][$j]];
            }
        }

        return $combinations;
    }

    /**
     * @param int $setCount
     * @param int $iPermutationSize
     * @param null $aPreviousPermutation
     * @return array|bool|null
     */
    public function combinationWithRepetitionWithNumbers($setCount, $iPermutationSize)
    {
        $vector = [];
        $combinations = [];
        $canGenerateMore = $this->generateNextCombinationWithRepetition($vector, $setCount, $iPermutationSize);
        $combinations[] = $vector;

        while ($canGenerateMore === true) {
            for ($x = 0; $x < $iPermutationSize; ++$x) {
                $canGenerateMore = $this->generateNextCombinationWithRepetition($vector, $setCount, $iPermutationSize);
                if ($canGenerateMore === true) {
                    $combinations[] = $vector;
                }
            }
        }

        return $combinations;
    }

    /**
     * Based on http://www.aconnect.de/friends/editions/computer/combinatoricode_e.html#k-combinations_with_repetition_in_lexicographic_order
     *
     * @param array $vector
     * @param int $n
     * @param int $k
     * @return bool will next generateCombinationWithRepeats on given vector provide next combination?
     */
    public function generateNextCombinationWithRepetition(&$vector, $n, $k)
    {
        if ($k > $n) {
            throw new \InvalidArgumentException("K [$k] can not be greater than N [$n]");
        }

        if (count($vector) === 0) {
            // initialize
            for ($j = 0; $j < $k; $j++) {
                $vector[$j] = 0;
            }

            return true;
        }

        //easy case, increase rightmost element
        if ($vector[$k - 1] < $n - 1) {
            $vector[$k - 1]++;
            return true;
        }

        //find rightmost element to increase
        for ($j = $k - 2; $j >= 0; $j--) {
            if ($vector[$j] != $n - 1) {
                break;
            }
        }

        //terminate if all elements are n - 1
        if ($j < 0) {
            return false;
        }

        //increase
        $vector[$j]++;

        //set right-hand elements
        for ($j += 1; $j < $k; $j++) {
            $vector[$j] = $vector[$j - 1];
        }

        return true;
    }

}
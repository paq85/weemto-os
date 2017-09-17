<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service;

/**
 * Knows the answer to all questions
 */
class Wiseacre 
{
    /**
     * @param array $subjects
     * @param string $getterName
     * @return mixed one of subjects
     * @throws \InvalidArgumentException
     */
    public function getMinimum(array $subjects, $getterName)
    {
        if (count($subjects) < 1) {
            throw new \InvalidArgumentException('No subjects provided');
        }

        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->$getterName() > $subjects[$i]->$getterName()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }

    /**
     * @param array $subjects
     * @param string $getterName
     * @return mixed one of subjects
     * @throws \InvalidArgumentException
     */
    public function getMaximum($subjects, $getterName)
    {
        if (count($subjects) < 1) {
            throw new \InvalidArgumentException('No subjects provided');
        }

        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->$getterName() < $subjects[$i]->$getterName()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }

    /**
     * @param MeasurableSizeInterface[] $subjects
     * @return mixed
     */
    public function getBroadest($subjects)
    {
        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->getSize() < $subjects[$i]->getSize()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }

    /**
     * @param MeasurableSizeInterface[] $subjects
     * @return mixed
     */
    public function getSmallest($subjects)
    {
        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->getSize() > $subjects[$i]->getSize()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }

    /**
     * @param MeasurablePopulationInterface[] $subjects
     * @return mixed
     */
    public function getMostPopulated($subjects)
    {
        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->getPopulation() < $subjects[$i]->getPopulation()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }

    /**
     * @param MeasurablePopulationInterface[] $subjects
     * @return mixed
     */
    public function getLeastPopulated($subjects)
    {
        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->getPopulation() > $subjects[$i]->getPopulation()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }

    /**
     * @param MeasurableUrbanizationInterface[] $subjects
     * @return mixed
     */
    public function getMostUrbanized($subjects)
    {
        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->getUrbanization() < $subjects[$i]->getUrbanization()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }

    /**
     * @param MeasurableUrbanizationInterface[] $subjects
     * @return mixed
     */
    public function getLeastUrbanized($subjects)
    {
        $theMostOne = $subjects[0];

        for ($i = 0, $count = count($subjects); $i < $count; ++$i) {
            if ($theMostOne->getUrbanization() > $subjects[$i]->getUrbanization()) {
                $theMostOne = $subjects[$i];
            }
        }

        return $theMostOne;
    }
}
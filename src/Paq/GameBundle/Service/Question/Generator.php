<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service\Question;


use Paq\GameBundle\Entity\Question;
use Paq\GameBundle\Entity\Tag;
use Paq\GameBundle\Entity\TagEnum;
use Paq\GameBundle\Entity\TagRepository;
use Paq\GameBundle\Service\Crook;
use Paq\GameBundle\Service\Ombach;
use Paq\GameBundle\Service\Wiseacre;

class Generator
{
    const HINTS_COUNT = 3;

    const QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST = 'Które z wymienionych województw jest NAJWIĘKSZE?';
    const QUESTION_TEXT_WHICH_PROVINCE_IS_THE_SMALLEST = 'Które z wymienionych województw jest NAJMNIEJSZE?';
    const QUESTION_TEXT_WHICH_IS_THE_MOST_POPULATED = 'Które z wymienionych województw zamieszkuje NAJWIĘCEJ ludzi?';
    const QUESTION_TEXT_WHICH_IS_THE_LEAST_POPULATED = 'Które z wymienionych województw zamieszkuje NAJMNIEJ ludzi?';
    const QUESTION_TEXT_WHICH_IS_THE_MOST_URBANIZED = 'Które z wymienionych województw jest NAJBARDZIEJ zurbanizowane?';
    const QUESTION_TEXT_WHICH_IS_THE_LEAST_URBANIZED = 'Które z wymienionych województw jest NAJMNIEJ zurbanizowane?';

    /**
     * @var Wiseacre
     */
    private $wiseacre;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @param TagRepository $tagRepository
     */
    public function __construct(TagRepository $tagRepository = null)
    {
        $this->wiseacre = new Wiseacre();
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param PolishProvince[] $provincesData
     * @return Question[]
     */
    public function generateQuestionsAboutPolishProvince($provincesData)
    {
        $wiseacre = $this->wiseacre;
        $appendIterator = new \AppendIterator();
        $tags = [
            $this->tagRepository->get(TagEnum::GEOGRAPHY),
            $this->tagRepository->get(TagEnum::_GENERATED),
            $this->tagRepository->get(TagEnum::_GENERATED_POLISH_PROVINCE),
            $this->tagRepository->get(TagEnum::POLAND)
        ];

        $appendIterator->append($this->generateWhichOneIsTheBiggest(
            self::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_BIGGEST,
            $provincesData,
            $tags
        ));
        $appendIterator->append($this->generateWhichOneIsTheSmallest(
            self::QUESTION_TEXT_WHICH_PROVINCE_IS_THE_SMALLEST,
            $provincesData,
            $tags
        ));
        $appendIterator->append($this->generateWhichOneIsMostPopulated(
            self::QUESTION_TEXT_WHICH_IS_THE_MOST_POPULATED,
            $provincesData,
            $tags
        ));
        $appendIterator->append($this->generateWhichOneIsLeastPopulated(
            self::QUESTION_TEXT_WHICH_IS_THE_MOST_POPULATED,
            $provincesData,
            $tags
        ));
        $appendIterator->append($this->generateWhichOneIsMostUrbanized(
            self::QUESTION_TEXT_WHICH_IS_THE_MOST_URBANIZED,
            $provincesData,
            $tags
        ));
        $appendIterator->append($this->generateWhichOneIsLeastUrbanized(
            self::QUESTION_TEXT_WHICH_IS_THE_MOST_URBANIZED,
            $provincesData,
            $tags
        ));

        $appendIterator->append($this->generateCombinationWithoutRepetition(
                'W którym województwie jest NAJWIĘKSZE bezrobocie?',
                $provincesData,
                function($places) use ($wiseacre) {
                    return $wiseacre->getMaximum($places, 'getUnemployment');
                },
                $tags
            )
        );

        $appendIterator->append($this->generateCombinationWithoutRepetition(
                'W którym województwie jest NAJMNIEJSZE bezrobocie?',
                $provincesData,
                function($places) use ($wiseacre) {
                    return $wiseacre->getMinimum($places, 'getUnemployment');
                },
                $tags
            )
        );

        $appendIterator->append($this->generateCombinationWithoutRepetition(
                'W którym województwie jest NAJWIĘKSZE PKB na osobę?',
                $provincesData,
                function($places) use ($wiseacre) {
                    return $wiseacre->getMaximum($places, 'getPkbPerPerson');
                },
                $tags
            )
        );

        $appendIterator->append($this->generateCombinationWithoutRepetition(
                'W którym województwie jest NAJMNIEJSZE PKB na osobę?',
                $provincesData,
                function($places) use ($wiseacre) {
                    return $wiseacre->getMinimum($places, 'getPkbPerPerson');
                },
                $tags
            )
        );

        return $appendIterator;
    }

    /**
     * @param Country[] $countries
     * @return Question[]
     */
    public function generateQuestionsAboutCountries($countries)
    {
        $wiseacre = $this->wiseacre;
        $tags = [
            $this->tagRepository->get(TagEnum::GEOGRAPHY),
            $this->tagRepository->get(TagEnum::_GENERATED),
            $this->tagRepository->get(TagEnum::_GENERATED_COUNTRY)
        ];

        $appendIterator = new \AppendIterator();

        $appendIterator->append($this->generateCombinationWithoutRepetition(
                'Który z wymienionych krajów jest NAJWIĘKSZY?',
                $countries,
                function($places) use ($wiseacre) {
                    return $wiseacre->getMaximum($places, 'getSize');
                },
                $tags
            )
        );

        $appendIterator->append($this->generateCombinationWithoutRepetition(
                'Który z wymienionych krajów jest NAJMNIEJSZY?',
                $countries,
                function($places) use ($wiseacre) {
                    return $wiseacre->getMinimum($places, 'getSize');
                },
                $tags
            )
        );

        return $appendIterator;
    }

    /**
     * @param string $questionText
     * @param array $subjects
     * @param callable $findProperAnswerSubject
     * @return Question[]
     */
    public function generateCombinationWithoutRepetition($questionText, $subjects, callable $findProperAnswerSubject, $tags = [])
    {
        return new GeneratorResult($subjects, $questionText, $findProperAnswerSubject, $tags);
    }

    /**
     * @param string $questionText
     * @param object[] $subjects
     * @return Question[]
     */
    public function generateWhichOneIsTheBiggest($questionText, $subjects, $tags = [])
    {
        $wiseacre = $this->wiseacre;

        return $this->generateCombinationWithoutRepetition(
            $questionText,
            $subjects,
            function($places) use ($wiseacre) {
                return $wiseacre->getBroadest($places);
            },
            $tags
        );
    }

    /**
     * @param string $questionText
     * @param object[] $subjects
     * @return Question[]
     */
    public function generateWhichOneIsTheSmallest($questionText, $subjects, $tags = [])
    {
        $wiseacre = $this->wiseacre;

        return $this->generateCombinationWithoutRepetition(
            $questionText,
            $subjects,
            function($places) use ($wiseacre) {
                return $wiseacre->getSmallest($places);
            },
            $tags
        );
    }

    /**
     * @param string $questionText
     * @param object[] $subjects
     * @return Question[]
     */
    public function generateWhichOneIsMostPopulated($questionText, $subjects, $tags = [])
    {
        $wiseacre = $this->wiseacre;

        return $this->generateCombinationWithoutRepetition(
            $questionText,
            $subjects,
            function($places) use ($wiseacre) {
                return $wiseacre->getMostPopulated($places);
            },
            $tags
        );
    }

    /**
     * @param string $questionText
     * @param object[] $subjects
     * @return Question[]
     */
    public function generateWhichOneIsLeastPopulated($questionText, $subjects, $tags = [])
    {
        $wiseacre = $this->wiseacre;

        return $this->generateCombinationWithoutRepetition(
            $questionText,
            $subjects,
            function($places) use ($wiseacre) {
                return $wiseacre->getLeastPopulated($places);
            },
            $tags
        );
    }

    /**
     * @param string $questionText
     * @param array $subjects
     * @return \Paq\GameBundle\Entity\Question[]
     */
    public function generateWhichOneIsMostUrbanized($questionText, $subjects, $tags = [])
    {
        $wiseacre = $this->wiseacre;

        return $this->generateCombinationWithoutRepetition(
            $questionText,
            $subjects,
            function($places) use ($wiseacre) {
                return $wiseacre->getMostUrbanized($places);
            },
            $tags
        );
    }

    /**
     * @param string $questionText
     * @param array $subjects
     * @return \Paq\GameBundle\Entity\Question[]
     */
    public function generateWhichOneIsLeastUrbanized($questionText, $subjects, $tags = [])
    {
        $wiseacre = $this->wiseacre;

        return $this->generateCombinationWithoutRepetition(
            $questionText,
            $subjects,
            function($places) use ($wiseacre) {
                return $wiseacre->getLeastUrbanized($places);
            },
            $tags
        );
    }

    /**
     * @param int $highestNumber
     * @return Question[]
     */
    public function generateMathSumQuestions($highestNumber)
    {
        $crook = new Crook();

        $rawCombinations = (new Ombach())->combinationWithRepetitionWithNumbers($highestNumber, 2);
        $questions = [];
        $tags = [
            $this->tagRepository->get(TagEnum::MATH),
            $this->tagRepository->get(TagEnum::_GENERATED),
            $this->tagRepository->get(TagEnum::_GENERATED_MATH)
        ];

        foreach ($rawCombinations as $rawCombination) {
            $a = $rawCombination[0];
            $b = $rawCombination[1];
            $properAnswer = $a + $b;

            $question = new Question();
            $question->setText(sprintf('%s + %s = ?', $a, $b));
            $hints = array_merge([$properAnswer], $crook->getSum($a, $b, 2));
            shuffle($hints);
            $question->setHints($hints);
            $question->setProperAnswer($properAnswer);
            $question->setTags($tags);

            $questions[] = $question;
        }

        return $questions;
    }

    /**
     * @param int $highestNumber
     * @return Question[]
     */
    public function generateMathDifferenceQuestions($highestNumber)
    {
        $crook = new Crook();

        $rawCombinations = (new Ombach())->combinationWithRepetitionWithNumbers($highestNumber, 2);
        $questions = [];
        $tags = [
            $this->tagRepository->get(TagEnum::MATH),
            $this->tagRepository->get(TagEnum::_GENERATED),
            $this->tagRepository->get(TagEnum::_GENERATED_MATH)
        ];

        foreach ($rawCombinations as $rawCombination) {
            $a = $rawCombination[0];
            $b = $rawCombination[1];
            $properAnswer = $a - $b;

            $question = new Question();
            $question->setText(sprintf('%s - %s = ?', $a, $b));
            $hints = array_merge([$properAnswer], $crook->getDifference($a, $b, 2));
            shuffle($hints);
            $question->setHints($hints);
            $question->setProperAnswer($properAnswer);
            $question->setTags($tags);

            $questions[] = $question;
        }

        return $questions;
    }

    /**
     * @param int $highestNumber
     * @return Question[]
     */
    public function generateMathProductQuestions($highestNumber)
    {
        $crook = new Crook();

        $rawCombinations = (new Ombach())->combinationWithRepetitionWithNumbers($highestNumber, 2);
        $questions = [];
        $tags = [
            $this->tagRepository->get(TagEnum::MATH),
            $this->tagRepository->get(TagEnum::_GENERATED),
            $this->tagRepository->get(TagEnum::_GENERATED_MATH)
        ];

        foreach ($rawCombinations as $rawCombination) {
            $a = $rawCombination[0];
            $b = $rawCombination[1];
            $properAnswer = $a * $b;

            $question = new Question();
            $question->setText(sprintf('%s × %s = ?', $a, $b));
            $hints = array_merge([$properAnswer], $crook->getProduct($a, $b, 2));
            shuffle($hints);
            $question->setHints($hints);
            $question->setProperAnswer($properAnswer);
            $question->setTags($tags);

            $questions[] = $question;
        }

        return $questions;
    }
} 
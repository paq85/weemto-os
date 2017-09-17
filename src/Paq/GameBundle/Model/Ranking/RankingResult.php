<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Model\Ranking;

final class RankingResult
{
    /**
     * @var int
     */
    private $place;

    /**
     * @var string
     */
    private $username;

    /**
     * @var int
     */
    private $score;

    /**
     * @param int $place
     * @param string $username
     * @param int $score
     */
    public function __construct($username, $score, $place = 1)
    {
        $this->place = (int) $place;
        $this->username = (string) $username;
        $this->score = (int) $score;
    }

    /**
     * @return int
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param int $place
     */
    public function setPlace($place)
    {
        $this->place = (int) $place;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }
}
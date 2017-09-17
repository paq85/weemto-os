<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Info about User getting score(s)
 *
 * @ORM\Entity(repositoryClass="Paq\GameBundle\Entity\UserScoreLogRepository")
 */
class UserScoreLog
{
    /**
     * @ORM\Column(type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string UUID v4
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Paq\GameBundle\Entity\User")
     * @var User
     */
    private $user;

    /**
     * Games are often removed so we don't keep relation here
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    private $gameId;

    /**
     * @ORM\ManyToOne(targetEntity="Paq\GameBundle\Entity\Question")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @var Question
     */
    private $question;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $pointsWon;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $dateTime;

    /**
     * @param string $id UUID (v4)
     * @param User $user
     * @param int $gameId
     * @param Question $question
     * @param int $pointsWon
     * @param \DateTime $dateTime
     */
    public function __construct($id, User $user, $gameId, Question $question, $pointsWon, \DateTime $dateTime)
    {
        $this->id = $id;
        $this->user = $user;
        $this->gameId = $gameId;
        $this->question = $question;
        $this->pointsWon = $pointsWon;
        $this->dateTime = $dateTime;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return int
     */
    public function getPointsWon()
    {
        return $this->pointsWon;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

}
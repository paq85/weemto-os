<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Sonata\TranslationBundle\Traits\Gedmo\PersonalTranslatable;

/**
 * @ORM\Entity
 * @ORM\Table
 * @Gedmo\TranslationEntity(class="Paq\GameBundle\Entity\QuestionHintTranslation")
 */
class QuestionHint implements TranslatableInterface
{
    use PersonalTranslatable;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Translatable()
     * @var string
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="Paq\GameBundle\Entity\Question", inversedBy="hints")
     * @ORM\JoinColumn(name="questionId", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Question
     */
    private $question;

    /**
     * @var \Paq\GameBundle\Entity\QuestionHintTranslation|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Paq\GameBundle\Entity\QuestionHintTranslation",
     *     mappedBy="object",
     *     cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @param Question|null $question
     * @param string|null $text
     */
    public function __construct(Question $question = null, $text = null)
    {
        $this->question = $question;
        $this->text = $text;
        $this->translations = new ArrayCollection();
    }

    /**
     * @param int $id
     * @deprecated FOR TESTING PURPOSES ONLY
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Question
     */
    public function setText($text)
    {
        if (! is_string($text) && ! is_numeric($text)) {
            throw new \InvalidArgumentException('Question Hint text must be a string. Got: ' . get_class($text));
        }

        $this->text = (string) $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    public function __toString()
    {
        return $this->getText();
    }

}

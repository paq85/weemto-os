<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use Paq\GameBundle\Model\EnabledLocaleInterface;
use Paq\GameBundle\PaqGameBundle;
use Sonata\MediaBundle\Model\Media;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Paq\GameBundle\Model\ImagePresentedInterface;
use Paq\GameBundle\Model\ImagePresentedTrait;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Sonata\TranslationBundle\Traits\Gedmo\PersonalTranslatable;

/**
 * @ORM\Entity(repositoryClass="Paq\GameBundle\Entity\QuestionRepository")
 * @ORM\Table
 * @Gedmo\TranslationEntity(class="Paq\GameBundle\Entity\QuestionTranslation")
 */
class Question implements ImagePresentedInterface, EnabledLocaleInterface, TranslatableInterface
{
    use ImagePresentedTrait, PersonalTranslatable;

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
     * @ORM\OneToMany(targetEntity="QuestionHint", mappedBy="question", cascade={"persist","remove"}, orphanRemoval=true)
     * @var QuestionHint[]
     */
    private $hints;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Translatable()
     * @var string
     */
    private $properAnswer;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", fetch="EXTRA_LAZY", inversedBy="questions")
     * @ORM\JoinTable(name="QuestionHasTags",
     *  joinColumns={@ORM\JoinColumn(name="questionId", referencedColumnName="id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="tagId", referencedColumnName="id", onDelete="CASCADE")}
     *  )
     * @var Tag[]|ArrayCollection
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
     **/
    private $image;

    /**
     * NOTE: format must reflect one of the formats specified in config.yml
     *
     * @var string
     */
    protected $defaultImageFormat = 'questions_small';

    /**
     * @ORM\Column(type="string", length=2, columnDefinition="ENUM('pl', 'en', '*')")
     * @var string
     */
    private $enabledLocale = PaqGameBundle::LOCALE_PL;

    /**
     * @var \Paq\GameBundle\Entity\QuestionTranslation|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Paq\GameBundle\Entity\QuestionTranslation",
     *     mappedBy="object",
     *     cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @param array|null $params
     */
    public function __construct($params = null)
    {
        $this->hints = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->translations = new ArrayCollection();

        isset($params['text']) ? $this->setText($params['text']) : null;
        isset($params['proper_answer']) ? $this->setProperAnswer($params['proper_answer']) : null;
        isset($params['hints']) ? $this->setHints($params['hints']) : null;
        isset($params['tags']) ? $this->setTags($params['tags']) : null;
    }

    /**
     * @param int $id
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
        $this->text = $text;

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

    /**
     * Set hints
     *
     * @param array $hints
     * @return Question
     */
    public function setHints($hints)
    {
        $this->hints->clear();

        foreach ($hints as $hint) {
            $hintObject = $hint;
            if (! is_object($hintObject)) {
                $hintObject = new QuestionHint();
                $hintObject->setText($hint);
            }

            $this->addHint($hintObject);
        }

        return $this;
    }

    /**
     * @param QuestionHint $hint
     */
    public function addHint(QuestionHint $hint)
    {
        $hint->setQuestion($this);
        $this->hints->add($hint);
    }

    /**
     * @param QuestionHint $hint
     */
    public function removeHint(QuestionHint $hint)
    {
        $this->hints->removeElement($hint);
    }

    /**
     * Get hints
     *
     * @return QuestionHint[]
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * @return string[]
     */
    public function getHintsText()
    {
        $texts = [];
        foreach ($this->getHints() as $hint) {
            $texts[] = $hint->getText();
        }

        return $texts;
    }

    /**
     * Set properAnswer
     *
     * @param string $properAnswer
     * @return Question
     */
    public function setProperAnswer($properAnswer)
    {
        $this->properAnswer = $properAnswer;

        return $this;
    }

    /**
     * Get properAnswer
     *
     * @return string 
     */
    public function getProperAnswer()
    {
        return $this->properAnswer;
    }

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @param Tag $tag
     * @return bool
     */
    public function hasTag(Tag $tag)
    {
        return $this->tags->contains($tag);
    }

    /**
     * @param Media $image
     * @return $this
     */
    public function setImage(Media $image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasImage()
    {
        return (null !== $this->getImage());
    }

    /**
     * @return Media
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $enabledLocale
     * @return $this
     */
    public function setEnabledLocale($enabledLocale)
    {
        $this->enabledLocale = $enabledLocale;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEnabledLocale()
    {
        return $this->enabledLocale;
    }

    /**
     * Checks if given question is equal to this question
     *
     * @param Question $question
     * @return bool TRUE if Text, Proper answer and Hints are the same
     */
    public function equals(Question $question)
    {
        if ($this->getText() !== $question->getText()) {
            return false;
        }

        if ($this->getProperAnswer() !== $question->getProperAnswer()) {
            return false;
        }

        if (count($this->getHints()) !== count(array_intersect($this->getHintsText(), $question->getHintsText()))) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getText();
    }
}

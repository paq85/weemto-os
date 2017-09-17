<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Paq\GameBundle\Model\EnabledLocaleInterface;
use Paq\GameBundle\PaqGameBundle;
use Sonata\MediaBundle\Model\Media;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Paq\GameBundle\Model\ImagePresentedInterface;
use Paq\GameBundle\Model\ImagePresentedTrait;
use Sonata\TranslationBundle\Traits\Gedmo\PersonalTranslatable;

/**
 * @ORM\Entity(repositoryClass="Paq\GameBundle\Entity\TagRepository")
 * @ORM\Table(name="Tag",uniqueConstraints={@ORM\UniqueConstraint(name="TagUniqueIdx", columns={"name"})})
 * @Gedmo\TranslationEntity(class="Paq\GameBundle\Entity\TagTranslation")
 */
class Tag implements ImagePresentedInterface, EnabledLocaleInterface, TranslatableInterface
{
    use ImagePresentedTrait, PersonalTranslatable;

    const TYPE_CATEGORY = '10';
    const TYPE_GENERATED = '20';
    const TYPE_CHALLENGE = '30';

    /**
     * @var int[]
     */
    private static $types = [
        self::TYPE_CATEGORY,
        self::TYPE_GENERATED,
        self::TYPE_CHALLENGE,
    ];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Translatable()
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     * @var int One of TYPE_* consts
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\Translatable()
     * @var string
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media")
     **/
    private $image;

    /**
     * NOTE: format must reflect one of the formats specified in config.yml
     *
     * @var string
     */
    protected $defaultImageFormat = 'tags_small';

    /**
     * @ORM\Column(type="string", length=2, columnDefinition="ENUM('pl', 'en', '*')")
     * @var string
     */
    private $enabledLocale = PaqGameBundle::LOCALE_ANY;

    /**
     * @ORM\ManyToMany(targetEntity="Paq\GameBundle\Entity\Question", mappedBy="tags", fetch="EXTRA_LAZY")
     * @var Question[]|ArrayCollection
     */
    private $questions;

    /**
     * @var \Paq\GameBundle\Entity\TagTranslation|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Paq\GameBundle\Entity\TagTranslation",
     *     mappedBy="object",
     *     cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isFeatured;

    /**
     * @param string $name
     * @param string $type
     */
    public function __construct($name = '', $type = self::TYPE_CATEGORY)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setDescription($name);
        $this->questions = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->isFeatured = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @deprecated For testing purposes only
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        if (!in_array($type, self::$types)) {
            throw new \InvalidArgumentException('Unknown Tag type = ' . $type);
        }

        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isChallenge()
    {
        return $this->getType() === Tag::TYPE_CHALLENGE;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * @return Question[]|ArrayCollection
     */
    public function getQuestions()
    {
        return $this->questions;
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
     * @return boolean
     */
    public function isFeatured()
    {
        return $this->isFeatured;
    }

    /**
     * @param boolean $isFeatured
     * @return Tag
     */
    public function setIsFeatured($isFeatured)
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

}
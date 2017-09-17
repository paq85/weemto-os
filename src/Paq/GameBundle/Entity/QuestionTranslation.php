<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\AbstractPersonalTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="QuestionTranslation",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="lookup_unique_question_translation_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class QuestionTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Paq\GameBundle\Entity\Question", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
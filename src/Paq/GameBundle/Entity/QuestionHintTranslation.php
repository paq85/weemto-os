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
 * @ORM\Table(name="QuestionHintTranslation",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="lookup_unique_question_hint_translation_idx", columns={
 *         "locale", "object_id", "field"
 *     })}
 * )
 */
class QuestionHintTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Paq\GameBundle\Entity\QuestionHint", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}
<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Symfony\Twig\Extensions;


use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\LoggingTranslator;
use Symfony\Component\Translation\TranslatorInterface;

class TranslatorExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('translate', array($this, 'translate')),
        );
    }

    public function translate($string)
    {
        if (stripos($string, 'tag.#') === 0) {
            // it's a challenge tag; Do NOT translate - just trim "tag."
            return substr($string, 4);
        } else {
            return $this->translator->trans($string);
        }
    }

    public function getName()
    {
        return 'translator_extension';
    }
}
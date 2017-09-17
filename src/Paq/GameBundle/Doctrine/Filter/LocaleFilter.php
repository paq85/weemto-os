<?php

namespace Paq\GameBundle\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Paq\GameBundle\PaqGameBundle;

/**
 * NOTICE
 * This filter has been manually enabled in QuestionRepository::getRandomQuestion because it's using Native Queries
 */
class LocaleFilter extends SQLFilter
{
    const NAME = 'locale_filter';

    const MANUALLY_ENABLED_HERE = 'use this const every place this filter is enabled manually';

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // Check if the entity implements the EnabledLocaleInterface
        if (!$targetEntity->reflClass->implementsInterface('\Paq\GameBundle\Model\EnabledLocaleInterface')) {
            return "";
        }

        try {
            // getParameter applies quoting automatically
            return $targetTableAlias.'.enabledLocale IN ('
                . $this->getParameter('enabledLocale') . ", '" . PaqGameBundle::LOCALE_ANY . "'"
                . ')';
        } catch (\InvalidArgumentException $iex) {
            return ''; // most likely "enabledLocale" parameter not set
        }
    }
}
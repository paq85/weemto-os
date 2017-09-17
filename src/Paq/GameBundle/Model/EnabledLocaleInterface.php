<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Model;

/**
 * Marks object as locale aware - should be visible in specific locale only
 */
interface EnabledLocaleInterface
{
    /**
     * @return string eg. 'pl' or 'en'
     */
    public function getEnabledLocale();
}
<?php

namespace Paq\GameBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class PaqGameBundle extends Bundle
{
    const LOCALE_PL = 'pl';
    const LOCALE_EN = 'en';
    // special const for some cases, eg. fetching from database without worrying about the locale
    const LOCALE_ANY = '*';

    /**
     * @var string[] Bundle supported locales
     */
    public static $LOCALES = [self::LOCALE_PL, self::LOCALE_EN];

    /**
     * @param string $locale
     * @return bool
     */
    public static function supportsLocale($locale)
    {
        return in_array($locale, self::$LOCALES);
    }
}

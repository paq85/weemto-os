<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\Pro\String;


final class Strings
{
    public static function removeNewLine($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return stripos($haystack, $needle) === 0;
    }
}
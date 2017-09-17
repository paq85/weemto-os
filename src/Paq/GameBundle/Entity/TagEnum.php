<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;


abstract class TagEnum
{
    const _GENERATED = '@gen'; // use this ONLY if question has been automatically generated and there's a lot of similar questions
    const _GENERATED_POLISH_PROVINCE = '@gen:geography-polish-province';
    const _GENERATED_COUNTRY = '@gen:geography-country';
    const _GENERATED_MATH = '@gen:math';

    const VARIOUS = 'various'; // use this ONLY if it's hard to use other tag(s)
    const MATH = 'math'; // matematyka
    const GEOGRAPHY = 'geography'; // geografia
    const BIOLOGY = 'biology'; // biologia
    const PHYSICS = 'physics'; // fizyka
    const HISTORY = 'history'; // historia
    const POLITICS = 'politics'; // polityka
    const GRAMMAR = 'grammar'; // gramatyka
    const POLAND = 'poland'; // polska
    const POLISH = 'polish'; // język polski
    const ENGLISH = 'english'; // język angielski
    const CINEMA = 'cinema'; // film, teatr, kino
    const NATURE = 'nature'; // przyroda
    const ANIMALS = 'animals'; // zwierzęta
    const PLANT = 'plant'; // rośliny
    const HEALTH = 'health'; // zdrowie
    const FOOD = 'food'; // jedzenie, pokarm
    const SPORT = 'sport';
    const FOOTBALL = 'football'; // aka soccer; piłka nożna
    const CELEBRITY = 'celebrity'; // famous person; słynne/znane osoby
    const MYTHOLOGY = 'mythology'; // mitologia
    const LITERATURE = 'literature'; // literatura / książki
    const COSMOS = 'cosmos'; // kosmos / wszechświat
    const SAYING = 'saying'; // powiedzenia
    const RELIGION = 'religion'; // religie
    const MUSIC = 'music'; // muzyka
    const MUSIC_CLASSICAL = 'music-classical'; // muzyka klasyczna
    const MUSIC_CONTEMPORARY = 'music-contemporary'; // muzyka współczesna
    const TECHNOLOGY = 'technology'; // technologia - technika
    const IT = 'it'; // urządzenia i rozwiązania z IT
    const INTERNET = 'internet';
    const INVENTION = 'invention'; // wynalazek
    const BUSINESS = 'business'; // firmy, producenci
    const BRAND = 'brand'; // znane marki, logo
    const DESIGN = 'design'; // design, projektowanie

    /**
     * @var string[] List of all Categories Tags
     */
    public static $categories = [
        self::VARIOUS,
        self::MATH, self::HISTORY, self::POLITICS, self::GEOGRAPHY, self::BIOLOGY, self::PHYSICS, self::GRAMMAR,
        self::CINEMA,
        self::POLAND, self::POLISH, self::ENGLISH,
        self::NATURE, self::ANIMALS, self:: PLANT, self::HEALTH, self::FOOD, self::SPORT, self::FOOTBALL, self::CELEBRITY,
        self::MYTHOLOGY, self::LITERATURE, self::COSMOS, self::SAYING, self::RELIGION,
        self::MUSIC, self::MUSIC_CLASSICAL, self::MUSIC_CONTEMPORARY,
        self::TECHNOLOGY, self::IT, self::INTERNET, self::INVENTION,
        self::BUSINESS, self::BRAND, self::DESIGN
    ];

    public static $generated = [
        self::_GENERATED, self::_GENERATED_POLISH_PROVINCE, self::_GENERATED_COUNTRY, self::_GENERATED_MATH,
    ];
}
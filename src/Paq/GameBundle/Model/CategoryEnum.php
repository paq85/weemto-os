<?php

namespace Paq\GameBundle\Model;


/**
 * List of Categories used
 *
 * Based on Wikipedia's categories
 *
 * @see https://en.wikipedia.org/wiki/Portal:Contents/Categories
 * @see https://pl.wikipedia.org/wiki/Portal:Portale
 */
abstract class CategoryEnum
{

    // MAIN CATEGORIES
    const CULTURE_AND_ARTS = 'CULTURE_AND_ARTS';
    const GEOGRAPHY_AND_PLACES = 'GEOGRAPHY_AND_PLACES';
    const HEALTH_AND_FITNESS = 'HEALTH_AND_FITNESS';
    const HISTORY_AND_EVENTS = 'HISTORY_AND_EVENTS';
    const MATHEMATICS_AND_LOGIC = 'MATHEMATICS_AND_LOGIC';
    const NATURAL_AN_PHYSICAL_SCIENCES = 'NATURAL_AN_PHYSICAL_SCIENCES';
    const PEOPLE_AND_SELF = 'PEOPLE_AND_SELF';
    const PHILOSOPHY_AND_THINKING = 'PHILOSOPHY_AND_THINKING';
    const RELIGION_AND_BELIEF_SYSTEMS = 'RELIGION_AND_BELIEF_SYSTEMS';
    const SOCIETY_AND_SOCIAL_SCIENCES = 'SOCIETY_AND_SOCIAL_SCIENCES';
    const TECHNOLOGY_AND_APPLIED_SCIENCES = 'TECHNOLOGY_AND_APPLIED_SCIENCES';

    // SUB-CATEGORIES
    /// CULTURE_AND_ARTS
    const GAMES_AND_TOYS = 'GAMES_AND_TOYS';
    const SPORTS_AND_RECREATION = 'SPORTS_AND_RECREATION';
    const MASS_MEDIA = 'MASS_MEDIA';
    /// GEOGRAPHY_AND_PLACES
    const EARTH = 'EARTH';
    const WORLD = 'WORLD';
    /// HEALTH_AND_FITNESS
    const MEDICINE = 'MEDICINE';
    /// HISTORY_AND_EVENTS
    /// MATHEMATICS_AND_LOGIC
    /// NATURAL_AN_PHYSICAL_SCIENCES
    const BIOLOGY = 'BIOLOGY';
    const GEOGRAPHY = 'GEOGRAPHY';
    const NATURE = 'NATURE';
    const PHYSICAL_SCIENCES = 'PHYSICAL_SCIENCES';
    //// PHYSICAL_SCIENCES
    const ASTRONOMY = 'ASTRONOMY';
    const CHEMISTRY = 'CHEMISTRY';
    const PHYSICS = 'PHYSICS';
    /// PEOPLE_AND_SELF
    /// PHILOSOPHY_AND_THINKING
    /// RELIGION_AND_BELIEF_SYSTEMS
    const MYTHOLOGY = 'MYTHOLOGY';
    /// SOCIETY_AND_SOCIAL_SCIENCES
    /// TECHNOLOGY_AND_APPLIED_SCIENCES
    const COMPUTING = 'COMPUTING';
    const ELECTRONICS = 'ELECTRONICS';
    const ENGINEERING = 'ENGINEERING';
}
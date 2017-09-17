<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Model;


class Country 
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $continent;

    /**
     * @var string
     */
    public $capital;

    /**
     * @var int
     */
    public $size;

    /**
     * @var int
     */
    public $population;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getContinent()
    {
        return $this->continent;
    }

    public function getCapital()
    {
        return $this->capital;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getPopulation()
    {
        return $this->population;
    }

    public function getPeopleDensity()
    {
        $this->getPopulation() / $this->getSize();
    }
} 
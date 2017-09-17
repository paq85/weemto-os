<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Model;


class PolishProvince implements MeasurableSizeInterface, MeasurablePopulationInterface, MeasurableUrbanizationInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $capital;

    /**
     * @var int km^2
     */
    public $size;

    /**
     * @var int
     */
    public $population;

    /**
     * @var float %
     */
    public $urbanization;

    /**
     * @var float %
     */
    public $unemployment;

    /**
     * @var int
     */
    public $pkbPerPerson;

    /**
     * @var string
     */
    public $licensePlate;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
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

    public function getUrbanization()
    {
        return $this->urbanization;
    }

    /**
     * @return float
     */
    public function getUnemployment()
    {
        return $this->unemployment;
    }

    /**
     * @return int
     */
    public function getPkbPerPerson()
    {
        return $this->pkbPerPerson;
    }

    /**
     * @return string
     */
    public function getLicensePlate()
    {
        return $this->licensePlate;
    }

}
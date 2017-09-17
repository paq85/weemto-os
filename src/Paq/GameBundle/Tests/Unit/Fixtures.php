<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Tests\Unit;


use Paq\GameBundle\Model\PolishProvince;

class Fixtures
{
    /**
     * @return PolishProvince[]
     */
    public function getPolishProvinces()
    {
        $places = [];

        $place = new PolishProvince('Dolnośląskie');
        $place->capitalCity = 'Wrocław';
        $place->size = 19946;
        $place->population = 2914362;
        $place->urbanization = 69.6;
        $place->unemployment = 13.2;
        $place->pkbPerPerson = 44961;
        $place->licensePlate = 'D';
        $places[] = $place;

        $place = new PolishProvince('Opolskie');
        $place->capitalCity = 'Opole';
        $place->size = 9411;
        $place->population = 1010203;
        $place->urbanization = 52.2;
        $place->unemployment = 14.3;
        $place->pkbPerPerson = 31771;
        $place->licensePlate = 'O';
        $places[] = $place;

        $place = new PolishProvince('Pomorskie');
        $place->capitalCity = 'Gdańsk';
        $place->size = 18310;
        $place->population = 2290070;
        $place->urbanization = 65.4;
        $place->unemployment = 13.3;
        $place->pkbPerPerson = 37822;
        $place->licensePlate = 'G';
        $places[] = $place;

        $place = new PolishProvince('Świętokrzyskie');
        $place->capitalCity = 'Kielce';
        $place->size = 11710;
        $place->population = 1273995;
        $place->urbanization = 44.9;
        $place->unemployment = 16.5;
        $place->pkbPerPerson = 29552;
        $place->licensePlate = 'T';
        $places[] = $place;

        return $places;
    }
} 
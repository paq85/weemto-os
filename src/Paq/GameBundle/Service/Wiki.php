<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service;


use Paq\GameBundle\Model\Country;
use Paq\GameBundle\Model\PolishProvince;

class Wiki
{
    /**
     * @return PolishProvince[]
     */
    public function getPolishProvinces()
    {
        $provinces = [];

        $rawData = array_map('str_getcsv', file(__DIR__ . '/../DataFixtures/Production/wiki/polish_provinces.csv'));

        foreach ($rawData as $provinceData) {
            $province = new PolishProvince($provinceData[0]);
            $province->capital = $provinceData[1];
            $province->size = floatval(str_replace(',', '.', $provinceData[2]));
            $province->population = intval($provinceData[3]);
            $province->urbanization = floatval(str_replace(',', '.', $provinceData[4]));
            $province->unemployment = floatval(str_replace(',', '.', $provinceData[5]));
            $province->pkbPerPerson = intval($provinceData[6]);
            $province->licensePlate = $provinceData[7];

            $provinces[] = $province;
        }


        return $provinces;
    }

    /**
     * @return Country[]
     */
    public function getCountries()
    {
        $countries = [];

        $rawData = array_map('str_getcsv', file(__DIR__ . '/../DataFixtures/Production/wiki/countries.csv'));

        foreach ($rawData as $countryData) {
            $country = new Country($countryData[1]);
            $country->continent = $countryData[3];
            $country->capital = $countryData[4];
            $country->size = intval(str_replace(' ', '', $countryData[5]));
            $country->population = intval(str_replace(' ', '', $countryData[6]));

            $countries[] = $country;
        }


        return $countries;
    }
} 
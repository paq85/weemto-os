<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Model;


interface MeasurableSizeInterface 
{
    /**
     * @return number
     */
    public function getSize();
} 
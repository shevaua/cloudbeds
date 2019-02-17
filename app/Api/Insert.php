<?php

namespace Api;

use Model\Interval;
use Action\Apply;

/**
 * Facade for Adding Interval
 */
class Insert
{

    /**
     * @param string $start
     * @param string $end
     * @param float $price
     */
    public function __construct(string $start, string $end, float $price)
    {

        $interval = new Interval([
            'start' => $start,
            'end' => $end,
            'price' => $price,          
        ]);

        new Apply($interval);

    }

}

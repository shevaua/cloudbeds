<?php

namespace Api;

use Model\Interval;
use Action\Apply;

class Insert
{

    public function __construct($start, $end, $price)
    {

        $interval = new Interval([
            'start' => $start,
            'end' => $end,
            'price' => $price,          
        ]);

        new Apply($interval);

    }

}

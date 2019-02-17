<?php

namespace Api;

use Model\Interval;
use Action\Clear;

class Delete
{

    public function __construct($start, $end)
    {

        $interval = new Interval([
            'start' => $start,
            'end' => $end,
        ]);

        new Clear($interval);

    }

}

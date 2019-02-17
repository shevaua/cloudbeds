<?php

namespace Api;

use Model\Interval;
use Action\Clear;

/**
 * Facade for Crearing interval
 */
class Delete
{

    /**
     * @param string $start
     * @param string $end
     */
    public function __construct(string $start, string $end)
    {

        $interval = new Interval([
            'start' => $start,
            'end' => $end,
        ]);

        new Clear($interval);

    }

}

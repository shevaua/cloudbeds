<?php

namespace Cli\Action;

use Interfaces\Runable;
use Model\Interval;

class Reset implements Runable
{

    public function run(array $params = [])
    {
        
        Interval::query()
            ->truncate();
        echo 'Table is clean' . PHP_EOL;

    }

}

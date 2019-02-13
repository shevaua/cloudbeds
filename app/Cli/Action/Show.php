<?php

namespace Cli\Action;

use Interfaces\Runable;
use Model\Interval;

class Show implements Runable
{

    public function run(array $params = [])
    {
        
        $intervals = Interval::query()
                ->order(['start' => 'asc'])
                ->find();

        foreach($intervals as $interval)
        {
            echo $interval . PHP_EOL;
        }

    }

}

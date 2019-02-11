<?php

namespace Cli\Action;

use Interfaces\Runable;
use Model\Price;

class Reset implements Runable
{

    public function run(array $params = [])
    {
        
        Price::truncate();
        echo 'Table is clean'.PHP_EOL;

    }

}

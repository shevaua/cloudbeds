<?php

namespace Cli\Action;

use Interfaces\Runable;
use Model\Price;

class Show implements Runable
{

    public function run(array $params = [])
    {
        
        $priceList = Price::query()
                ->order(['start' => 'asc'])
                ->find();

        foreach($priceList as $priceObj)
        {
            echo $priceObj.PHP_EOL;
        }

    }

}

<?php

namespace Cli\Action;

use Interfaces\Runable;
use Model\Price;

class Add implements Runable
{

    const REGEX_DATE = '#^\d{4}-\d{2}-\d{2}$#';

    public function run(array $params = [])
    {
        
        if(
            count($params) != 3
            or !$price = (int) $params[2]
            or $price < 0
            or !preg_match(self::REGEX_DATE, $params[0])
            or !preg_match(self::REGEX_DATE, $params[1])
            or !$start = strtotime($params[0])
            or !$end = strtotime($params[1])
            or $start > $end
        ) {
            echo 'Run cli/do.php add <start_date:YYYY-MM-DD> <end_date:YYYY-MM-DD> <price:INT>'.PHP_EOL;
            echo 'Note: start_date should be <= to end_date and price should be > 0'.PHP_EOL;
            return;
        }

        // simple insert here
        $priceObj = Price::create([
            'start' => $params[0],
            'end' => $params[1],
            'price' => $price
        ]);
        echo 'Added: '.$priceObj.PHP_EOL;

    }

}

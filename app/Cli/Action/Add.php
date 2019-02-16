<?php

namespace Cli\Action;

use Interfaces\Runable;
use Api\Insert;

class Add implements Runable
{

    const REGEX_DATE = '#^\d{4}-\d{2}-\d{2}$#';

    public function run(array $params = [])
    {
        
        if(
            count($params) == 2
            and $price = (float) $params[1]
            and $price > 0
            and preg_match(self::REGEX_DATE, $params[0])
            and $timestamp = strtotime($params[0])
        ) {
            new Insert($params[0], $params[0], $price);
            return;
        }

        if(
            count($params) == 3
            and $price = (float) $params[2]
            and $price > 0
            and preg_match(self::REGEX_DATE, $params[0])
            and preg_match(self::REGEX_DATE, $params[1])
            and $start = strtotime($params[0])
            and $end = strtotime($params[1])
            and $start <= $end
        ) {
            new Insert($params[0], $params[1], $price);
            return;            
        }

        echo 'Run cli/do.php add <date:YYYY-MM-DD> <price:FLOAT>'.PHP_EOL;
        echo 'Run cli/do.php add <start_date:YYYY-MM-DD> <end_date:YYYY-MM-DD> <price:FLOAT>'.PHP_EOL;
        echo 'Note: start_date should be <= to end_date and price should be > 0'.PHP_EOL;
        
    }

}

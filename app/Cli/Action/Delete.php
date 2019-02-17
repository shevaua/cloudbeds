<?php

namespace Cli\Action;

use Interfaces\Runable;
use Api\Delete as ApiDelete;
use Model\Interval;

class Delete implements Runable
{

    public function run(array $params = [])
    {
        
        if(
            count($params) == 1
            and preg_match(Interval::REGEX_DATE, $params[0])
            and $timestamp = strtotime($params[0])
        ) {
            new ApiDelete($params[0], $params[0]);
            return;
        }

        if(
            count($params) == 2
            and preg_match(Interval::REGEX_DATE, $params[0])
            and preg_match(Interval::REGEX_DATE, $params[1])
            and $start = strtotime($params[0])
            and $end = strtotime($params[1])
            and $start <= $end
        ) {
            new ApiDelete($params[0], $params[1]);
            return;            
        }

        echo 'Run cli/do.php delete <date:YYYY-MM-DD>'.PHP_EOL;
        echo 'Run cli/do.php delete <start_date:YYYY-MM-DD> <end_date:YYYY-MM-DD>'.PHP_EOL;
        echo 'Note: start_date should be <= to end_date'.PHP_EOL;
        
    }

}

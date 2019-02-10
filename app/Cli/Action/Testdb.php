<?php

namespace Cli\Action;

use Interfaces\Runable;
use DB\Connection;

class Testdb implements Runable
{

    public function run(array $params = [])
    {
        
        $connection = Connection::get();
        echo 'connection is ok' . PHP_EOL;

    }

}

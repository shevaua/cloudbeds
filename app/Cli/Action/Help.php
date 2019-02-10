<?php

namespace Cli\Action;

use Interfaces\Runable;

class Help implements Runable
{

    public function run(array $params = [])
    {

        echo 'Run cli/do.php <action> <param1> <param2> ...'.PHP_EOL;

    }

}

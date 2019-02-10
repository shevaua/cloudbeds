<?php

namespace Cli\Action;

use Interfaces\Runable;

class Help implements Runable
{

    public function run(array $params = [])
    {

        echo 'Run cli/do.php <action> <param1> <param2> ...'.PHP_EOL;
        echo 'List of actions:'.PHP_EOL;
        $paths = scandir(__DIR__);
        foreach($paths as $path)
        {
            if(preg_match('#^(\w+)\.php$#', $path, $matches))
            {
                echo '  > ' . strtolower($matches[1]) . PHP_EOL;
            }
        }

    }

}

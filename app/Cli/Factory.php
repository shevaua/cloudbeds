<?php

namespace Cli;

use Exceptions\Cli\WrongActionException;
use Throwable;

class Factory
{

    public function getAction($name)
    {

        $className = 'Cli\\Action\\' . ucfirst($name);
        try
        {
            $action = new $className;
            return $action;
        }
        catch(Throwable $e)
        {        
            throw new WrongActionException($name); 
        }

    }

}

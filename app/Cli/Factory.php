<?php

namespace Cli;

use Exceptions\Cli\WrongActionException;
use Throwable;
use Interfaces\Runable;

class Factory
{

    /**
     * @return Runable
     */
    public function getAction(string $name): Runable
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

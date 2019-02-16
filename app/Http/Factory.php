<?php

namespace Http;

use Throwable;
use Exceptions\Http\WrongControllerException;

class Factory
{

    public function getControllert($name)
    {

        $className = 'Http\\Controller\\' . $name;
        try
        {
            $instance = new $className;
            return $instance;
        }
        catch(Throwable $e)
        {        
            throw new WrongControllerException($name); 
        }

    }

}

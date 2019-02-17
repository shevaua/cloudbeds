<?php

namespace Http;

use Throwable;
use Exceptions\Http\WrongControllerException;

class Factory
{

    /**
     * Get controller instance
     * @param string $name
     */
    public function getControllert(string $name)
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

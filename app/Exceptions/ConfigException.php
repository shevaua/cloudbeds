<?php

namespace Exceptions;
 
use Exception;

class ConfigException extends Exception
{

    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? 'Unknown Config exception'
        );
    }

}

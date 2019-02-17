<?php

namespace Exceptions;
 
use Exception;

class ConfigException extends Exception
{

    public function __construct(string $message = '')
    {
        parent::__construct(
            $message ?? 'Unknown Config exception'
        );
    }

}

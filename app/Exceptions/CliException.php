<?php

namespace Exceptions;
 
use Exception;

class CliException extends Exception
{

    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? 'Unknown CLI exception'
        );
    }

}

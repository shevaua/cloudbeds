<?php

namespace Exceptions;
 
use Exception;

class DataException extends Exception
{

    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? 'Unknown Data exception'
        );
    }

}

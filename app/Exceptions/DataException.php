<?php

namespace Exceptions;
 
use Exception;

class DataException extends Exception
{

    public function __construct(string $message = '')
    {
        parent::__construct(
            $message ?? 'Unknown Data exception'
        );
    }

}

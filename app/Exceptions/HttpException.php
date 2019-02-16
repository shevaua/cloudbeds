<?php

namespace Exceptions;
 
use Exception;

class HttpException extends Exception
{

    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? 'Unknown Http exception'
        );
    }

}

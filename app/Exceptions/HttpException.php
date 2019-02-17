<?php

namespace Exceptions;
 
use Exception;

class HttpException extends Exception
{

    public function __construct(string $message = '')
    {
        parent::__construct(
            $message ?? 'Unknown Http exception'
        );
    }

}

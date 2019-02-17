<?php

namespace Exceptions;
 
use Exception;

class CliException extends Exception
{

    public function __construct(string $message = '')
    {
        parent::__construct(
            $message ?? 'Unknown CLI exception'
        );
    }

}

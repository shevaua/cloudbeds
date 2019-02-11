<?php

namespace Exceptions;
 
use Exception;

class SQLException extends Exception
{

    public function __construct($message, $query)
    {
        parent::__construct(
            $message . PHP_EOL.
            'Query: '.$query.PHP_EOL
        );
    }

}

<?php

namespace Exceptions;
 
use Exception;

class SQLException extends Exception
{

    /**
     * @param string $message
     * @param string $query
     */
    public function __construct(string $message, string $query)
    {
        parent::__construct(
            $message . PHP_EOL.
            'Query: '.$query.PHP_EOL
        );
    }

}

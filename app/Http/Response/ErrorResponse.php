<?php

namespace Http\Response;

use Http\Response;

class ErrorResponse extends Response
{

    public function __construct(\Throwable $e)
    {
        parent::__construct(
            500, 
            '<h1>'.$e->getMessage().'</h1>'.
            '<pre>'.$e->getTraceAsString().'</pre>'
        );
    }

}
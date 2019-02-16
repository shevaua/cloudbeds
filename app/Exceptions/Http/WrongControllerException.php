<?php

namespace Exceptions\Http;

use Exceptions\HttpException;

class WrongControllerException extends HttpException
{

    public function __construct($controller)
    {
        parent::__construct('Controller is missed: '.$controller);
    }

}

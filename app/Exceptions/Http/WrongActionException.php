<?php

namespace Exceptions\Http;

use Exceptions\HttpException;

class WrongActionException extends HttpException
{

    public function __construct(string $controller, string $action)
    {
        parent::__construct('Action is missed: '.$controller.'::'.$action);
    }

}

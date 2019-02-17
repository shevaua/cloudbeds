<?php

namespace Exceptions\Http;

use Exceptions\HttpException;

class WrongControllerException extends HttpException
{

    /**
     * @param string $controller
     */
    public function __construct(string $controller)
    {
        parent::__construct('Controller is missed: '.$controller);
    }

}

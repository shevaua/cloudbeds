<?php

namespace Exceptions\Http;

use Exceptions\HttpException;

class WrongViewException extends HttpException
{

    /**
     * @param string $controller
     * @param string $action
     */
    public function __construct(string $controller, string $action)
    {
        parent::__construct('View is missed in: '.$controller.'::'.$action);
    }

}

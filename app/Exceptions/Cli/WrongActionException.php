<?php

namespace Exceptions\Cli;

use Exceptions\CliException;

class WrongActionException extends CliException
{

    /**
     * @param string $actionName
     */
    public function __construct(string $actionName)
    {
        parent::__construct('Wrong action '.$actionName);
    }

}

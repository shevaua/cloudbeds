<?php

namespace Exceptions\Cli;

use Exceptions\CliException;

class WrongActionException extends CliException
{

    public function __construct($actionName)
    {
        parent::__construct('Wrong action '.$actionName);
    }

}

<?php

namespace Exceptions\Config;

use Exceptions\ConfigException;

class WrongConfigException extends ConfigException
{

    public function __construct($path)
    {
        parent::__construct('Wrong config format : '.$path);
    }

}

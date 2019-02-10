<?php

namespace Exceptions\Config;

use Exceptions\ConfigException;

class MissedConfigException extends ConfigException
{

    public function __construct($path)
    {
        parent::__construct('Config is missed: '.$path);
    }

}

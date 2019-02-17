<?php

namespace Exceptions\Config;

use Exceptions\ConfigException;

class MissedConfigException extends ConfigException
{

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct('Config is missed: '.$path);
    }

}

<?php

namespace Exceptions\Config;

use Exceptions\ConfigException;

class WrongConfigException extends ConfigException
{

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct('Wrong config format : '.$path);
    }

}

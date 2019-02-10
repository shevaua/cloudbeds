<?php

use Exceptions\Config\MissedConfigException;
use Exceptions\Config\WrongConfigException;

class Config
{

    const CONFIG_DIR = 'config';

    private static $instance;

    public static function getInstance()
    {

        if(!self::$instance)
        {
            self::$instance = new self;
        }
        return self::$instance;

    }


    private $params = [];

    public function __construct()
    {
        
        $projectPath = PROJECT_PATH;
        $env = Application::getInstance()
            ->getEnv();
        $configPath = $projectPath . '/'. self::CONFIG_DIR . '/' . $env . '.ini';

        if(
            !file_exists($configPath)
        ) {
            throw new MissedConfigException($configPath);
        }

        if(!$configData = parse_ini_file($configPath, true))
        {
            throw new WrongConfigException($configPath);
        }

        $this->params = $configData;

    }

    public function get($name)
    {

        $parts = explode('.', $name);

        $params = &$this->params;

        foreach($parts as $part)
        {

            if(!is_array($params))
            {
                return null;
            }

            if(!isset($params[$part]))
            {
                return null;
            }

            $params = &$params[$part];

        } 
        
        return $params;

    }

}

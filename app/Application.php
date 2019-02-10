<?php

use Exceptions\Cli\WrongActionException;

class Application
{

    const ENV_DEV = 'development';
    const ENV_PROD = 'production';

    const ENVS = [
        self::ENV_DEV,
        self::ENV_PROD,
    ];

    private static $instance;

    public static function getInstance()
    {

        if(!self::$instance)
        {
            self::$instance = new self;
            spl_autoload_register([__CLASS__, 'loader']);
        }
        return self::$instance;

    }

    public static function loader(string $className)
    {

        $filePath = APP_PATH .'/'
            .str_replace('\\', '/', $className)
            .'.php';

        if(!file_exists($filePath))
        {
            return false;
        }

        require_once $filePath;
        return true;

    }


    private $env;

    public function run()
    {

        $sapiName = php_sapi_name();
        if($sapiName == 'cli')
        {
            $this->runCli();   
        }
        else
        {
            $this->runWeb();
        }
    }

    private function runCli()
    {

        if(
            !$env = getenv('ENV')
            or !in_array($env, self::ENVS)
        ) {
            $env = self::ENV_DEV;
        }
        $this->env = $env;
        
        global $argv;
        $params = $argv;

        // removing script path
        array_shift($params);

        // getting action name
        if(count($params) < 1)
        {
            $name = 'help';            
        }
        else
        {
            $name = array_shift($params);
        }

        try
        {
            $factory = new Cli\Factory;
            $action = $factory->getAction($name);
            $action
                ->run($params);
        }
        catch(WrongActionException $e)
        {
            echo $e->getMessage() . PHP_EOL;
        }
        
    }

    private function runWeb()
    {

        if(
            !defined('APP_ENV')
            or !$env = APP_ENV
            or !in_array($env, self::ENVS)
        ) {
            $env = self::ENV_DEV;
        }
        $this->env = $env;
        
        die('use cli for now');
    }

    public function getEnv()
    {

        return $this->env;

    }    

}

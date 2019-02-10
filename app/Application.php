<?php

use Exceptions\Cli\WrongActionException;

class Application
{

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
        die('use cli for now');
    }

}

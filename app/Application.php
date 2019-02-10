<?php

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

        try
        {
            new Model\DateRange;
        }
        catch(Throwable $e)
        {
            echo $e->getMessage(); die;
        }

    }

}

<?php

use Http\Request;
use Http\Router;
use Http\Response;
use Http\Route;
use Exceptions\Http\WrongActionException as HttpActionException;
use Exceptions\Cli\WrongActionException;
use Http\Response\ErrorResponse;
use Http\Response\HtmlResponse;
use Http\Response\JsonResponse;
use View\HtmlView;
use View\JsonView;

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

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        if(
            !defined('APP_ENV')
            or !$env = APP_ENV
            or !in_array($env, self::ENVS)
        ) {
            $env = self::ENV_DEV;
        }
        $this->env = $env;
        
        try
        {

            $request = $this->getIncomeRequest();
        
            $route = Router::getRoute($request);

            $response = $this->wrapResponse($route, $request);

            $response->send();

        }
        catch(Throwable $e)
        {
            (new ErrorResponse($e))->send();
        }

    }

    public function getEnv()
    {

        return $this->env;

    }    

    private function getIncomeRequest(): Request
    {

        parse_str(file_get_contents('php://input'), $result);

        return new Request(
            $_SERVER['REQUEST_METHOD'],
            str_replace('?'.$_SERVER['QUERY_STRING'] , '', $_SERVER['REQUEST_URI']),
            $_GET + $_POST + $result
        );

    }

    private function wrapResponse(Route $route, Request $request): Response
    {

        $factory = new Http\Factory;

        $controller = $factory
            ->getControllert($route->getController());
    
        if(!method_exists($controller, $request->getMethod()))
        {
            throw new HttpActionException(get_class($controller), $request->getMethod());
        }

        $return = $controller->{$request->getMethod()}($request);

        if($return instanceof HtmlView)
        {
            return new HtmlResponse($return, $return->getCode());
        }
        elseif($return instanceof JsonView)
        {
            return new JsonResponse($return, $return->getCode());
        }

    }

}

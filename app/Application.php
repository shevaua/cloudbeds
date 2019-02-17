<?php

use Http\Request;
use Http\Router;
use Http\Response;
use Http\Route;
use Exceptions\Http\WrongActionException as HttpActionException;
use Exceptions\Http\WrongViewException;
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

    /**
     * @var Application $instance
     */
    private static $instance;

    /**
     * Create Application instance and register autoloader
     */
    public static function getInstance(): self
    {

        if(!self::$instance)
        {
            self::$instance = new self;
            spl_autoload_register([__CLASS__, 'loader']);
        }
        return self::$instance;

    }

    /**
     * Class loader
     * @param string $className
     * @return bool
     */
    public static function loader(string $className): bool
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

    /**
     * Environment
     * @var string $env
     */
    private $env;

    /**
     * Entry point for the app
     * @return void
     */
    public function run()
    {

        $sapiName = php_sapi_name();

        if($sapiName == 'cli')
        {
            // cli
            $this->runCli();   
        }
        else
        {
            // web
            $this->runWeb();
        }

    }

    /**
     * Entry point for cli
     */
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

        // Search and run cli action 
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

    /**
     * Entry point for cli
     * @return void
     */
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
        
        try
        {
            /**
             * @var Request $request
             */
            $request = $this->getIncomeRequest();
        
            /**
             * Search route for request
             * @var Route $route
             */
            $route = Router::getRoute($request);

            /**
             * Wrap response for route
             * @var Response $response
             */
            $response = $this->wrapResponse($route, $request);

            /**
             * Send Response
             */
            $response->send();

        }
        catch(Throwable $e)
        {
            (new ErrorResponse($e))->send();
        }

    }


    /**
     * Get Environment
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }    

    /**
     * Prepare Request
     * @return Request
     */
    private function getIncomeRequest(): Request
    {

        parse_str(file_get_contents('php://input'), $result);

        return new Request(
            $_SERVER['REQUEST_METHOD'],
            str_replace('?'.$_SERVER['QUERY_STRING'] , '', $_SERVER['REQUEST_URI']),
            $_GET + $_POST + $result
        );

    }

    /**
     * wrap data in response 
     * @param Route $route
     * @param Request $request
     * @return Response
     */
    private function wrapResponse(Route $route, Request $request): Response
    {

        // Search for controller
        $factory = new Http\Factory;
        $controller = $factory
            ->getControllert($route->getController());
    
        // Check for method
        if(!method_exists($controller, $request->getMethod()))
        {
            throw new HttpActionException(get_class($controller), $request->getMethod());
        }

        // Get view from controller
        $return = $controller->{$request->getMethod()}($request);

        // return response
        if($return instanceof HtmlView)
        {
            return new HtmlResponse($return, $return->getCode());
        }
        elseif($return instanceof JsonView)
        {
            return new JsonResponse($return, $return->getCode());
        }

        return new ErrorResponse(new WrongViewException(get_class($controller), $request->getMethod()));

    }

}

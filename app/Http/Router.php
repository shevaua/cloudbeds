<?php

namespace Http;

class Router
{

    private static $instance;

    /**
     * Get Router instance
     * @return self
     */
    public static function getInstance()
    {
        if(!self::$instance)
        {
            self::$instance = new self;
        }        
        return self::$instance;
    }

    /**
     * Facade for searchin route
     * @return Route
     */
    public static function getRoute(Request $r): Route
    {
        return self::getInstance()
            ->get($r);
    }

    private $routes = [];

    public function __construct()
    {
        $this->routes[] = new Route('/', 'Home');
        $this->routes[] = new Route('/api/interval', 'API\Interval');
        $this->routes[] = new Route('/api/interval/reset', 'API\Interval\Reset');
    }

    /**
     * Get Route
     * @return Route
     */
    public function get(Request $r): Route
    {

        foreach($this->routes as $route)
        {
            if($route->match($r))
            {
                return $route;
            }
        }

        return new Route('', 'NotFound');

    }

}

<?php

namespace Http;

class Router
{

    private static $instance;

    public static function getInstance()
    {
        if(!self::$instance)
        {
            self::$instance = new self;
        }        
        return self::$instance;
    }

    public static function getRoute(Request $r)
    {
        return self::getInstance()
            ->get($r);
    }

    private $routes = [];

    public function __construct()
    {
        $this->routes[] = new Route('/', 'Home');
        // $this->route
    }

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

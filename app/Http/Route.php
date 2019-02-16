<?php

namespace Http;

class Route
{

    private $path;
    private $controller;

    public function __construct(string $path, string $controller)
    {

        $this->path = $path;
        $this->controller = $controller;

    }

    public function match(Request $r): bool
    {
        if($this->path == $r->getPath())
        {
            return true;
        }
        return false;
    }

    public function getController()
    {
        return $this->controller;
    }

}

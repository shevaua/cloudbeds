<?php

namespace Http;

/**
 * Mapping between path and controller
 */
class Route
{

    private $path;
    private $controller;

    /**
     * @param string $path
     * @param string $controller
     */
    public function __construct(string $path, string $controller)
    {

        $this->path = $path;
        $this->controller = $controller;

    }

    /**
     * Check whether route match to request
     * @return bool
     */
    public function match(Request $r): bool
    {
        if($this->path == $r->getPath())
        {
            return true;
        }
        return false;
    }

    /**
     * Get controller name
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

}

<?php

namespace Http;

class Request
{

    const METHODS = [
        'get',
        'post',
        'delete',
        'patch',
    ];

    private $method;
    private $path;
    private $params;

    public function __construct(string $method, string $path, array $params)
    {
        $this->method = strtolower($method);
        $this->path = $path;
        $this->params = $params;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getParams()
    {
        return $this->params;
    }

}

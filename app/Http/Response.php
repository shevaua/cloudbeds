<?php

namespace Http;

class Response
{

    private $code;
    private $body;
    private $headers;

    public function __construct(int $code, string $body = '', array $headers = [])
    {
        $this->code = $code;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function send()
    {
        
        http_response_code($this->code);
        foreach($this->headers as $header => $value)
        {
            header($header.': '.$value);
        }
        echo $this->body;

    }

}

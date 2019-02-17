<?php

namespace Http;

class Response
{

    private $code;
    private $body;
    private $headers;

    /**
     * @param int $code
     * @param string $body
     * @param array $headers
     */
    public function __construct(int $code, string $body = '', array $headers = [])
    {
        $this->code = $code;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function send()
    {
        
        // send http code
        http_response_code($this->code);

        // send headers
        foreach($this->headers as $header => $value)
        {
            header($header.': '.$value);
        }

        // send body
        echo $this->body;

    }

}

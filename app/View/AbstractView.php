<?php

namespace View;

abstract class AbstractView
{

    private $code = 200;

    /**
     * @param int $code
     */
    public function __construct(int $code)
    {
        $this->code = $code;
    }

    /**
     * @return int $code
     */
    public function getCode(): int
    {
        return $this->code;
    }
    
    /**
     * Get view content
     * @return string
     */
    abstract function getContent(): string;

}

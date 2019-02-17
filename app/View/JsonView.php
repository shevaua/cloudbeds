<?php

namespace View;

class JsonView extends AbstractView
{

    /**
     * @var array $data
     */
    private $data = [];

    /**
     * @param array $data
     * @param int $code
     */
    public function __construct(array $data, int $code = 200)
    {

        parent::__construct($code);
        $this->data = $data;

    }

    /**
     * Get view content
     * @return string
     */
    public function getContent(): string 
    {
        return json_encode($this->data);
    }

}

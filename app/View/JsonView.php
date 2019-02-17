<?php

namespace View;

class JsonView extends AbstractView
{

    private $data;
    private $code;

    public function __construct(array $data, $code = 200)
    {

        $this->data = $data;
        $this->code = $code;

    }

    public function getContent()
    {
        return json_encode($this->data);
    }

    public function getCode()
    {
        return $this->code;
    }

}

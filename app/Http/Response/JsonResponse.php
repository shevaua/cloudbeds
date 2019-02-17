<?php

namespace Http\Response;

use Http\Response;
use View\AbstractView;
use View\JsonView;

class JsonResponse extends Response
{

    public function __construct(JsonView $view, $code = 200)
    {
        parent::__construct(
            $code, 
            $view->getContent(),
            ['Content-Type' => 'application/json']
        );
    }

}
<?php

namespace Http\Response;

use Http\Response;
use View\AbstractView;
use View\JsonView;

class JsonResponse extends Response
{

    /**
     * @param JsonView $view
     * @param int $code
     */
    public function __construct(JsonView $view, int $code = 200)
    {
        parent::__construct(
            $code, 
            $view->getContent(),
            ['Content-Type' => 'application/json']
        );
    }

}
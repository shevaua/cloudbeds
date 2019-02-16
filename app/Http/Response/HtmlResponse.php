<?php

namespace Http\Response;

use Http\Response;
use View\AbstractView;
use View\HtmlView;

class HtmlResponse extends Response
{

    public function __construct(HtmlView $view, $code = 200)
    {
        parent::__construct(
            $code, 
            $view->getContent()
        );
    }

}
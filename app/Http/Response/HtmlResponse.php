<?php

namespace Http\Response;

use Http\Response;
use View\AbstractView;
use View\HtmlView;

class HtmlResponse extends Response
{

    /**
     * @param HtmlView $view
     * @param int $code
     */
    public function __construct(HtmlView $view, int $code = 200)
    {
        parent::__construct(
            $code, 
            $view->getContent()
        );
    }

}
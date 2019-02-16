<?php

namespace Http\Controller;

use Http\Request;
use View\HtmlView;

class NotFound
{

    public function get(Request $r)
    {

        return new HtmlView('notfound', '', 404);
        
    }

}
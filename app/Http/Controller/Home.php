<?php

namespace Http\Controller;

use View\HtmlView;
use Http\Request;

class Home
{

    public function get(Request $r)
    {
     
        return new HtmlView('cloudbeds', 'home');

    }

}
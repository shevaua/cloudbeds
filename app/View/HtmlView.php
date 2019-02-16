<?php

namespace View;

class HtmlView extends AbstractView
{


    private $layout;
    private $page;
    private $code;

    public function __construct(string $layout, string $page = '', $code = 200)
    {

        $this->layout = $layout;
        $this->page = $page;
        $this->code = $code;

    }

    public function getContent()
    {
        extract(['content' => $this->getPageContent()]);
        ob_start();
        include PROJECT_PATH . '/views/layout/'.$this->layout.'.php';
        $content = ob_get_clean();
        return $content;
    }

    private function getPageContent()
    {
        if(!$this->page)
        {
            return '';
        }
        ob_start();
        include PROJECT_PATH . '/views/'.$this->layout.'/'.$this->page.'.php';
        $content = ob_get_clean();
        return $content;
    }

    public function getCode()
    {
        return $this->code;
    }

}

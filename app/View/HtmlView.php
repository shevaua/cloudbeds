<?php

namespace View;

class HtmlView extends AbstractView
{

    /**
     * @var string $layout
     * @var string $page
     */
    private $layout;
    private $page;

    /**
     * @param string $layout
     * @param string $page
     * @param int $code
     */
    public function __construct(string $layout, string $page = '', int $code = 200)
    {

        parent::__construct($code);
        $this->layout = $layout;
        $this->page = $page;

    }

    /**
     * Get view content
     * @return string
     */
    public function getContent(): string
    {
        extract(['content' => $this->getPageContent()]);
        ob_start();
        include PROJECT_PATH . '/views/layout/'.$this->layout.'.php';
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Get page view content
     * @return string
     */
    private function getPageContent(): string
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

}

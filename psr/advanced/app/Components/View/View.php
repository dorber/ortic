<?php

namespace App\Components\View;


/**
 * Class View
 * @package App\Components\View
 */
class View implements ViewInterface
{

    protected $viewsDir;


    /**
     * View constructor.
     * @param $viewsDir
     */
    public function __construct($viewsDir)
    {
        $this->viewsDir = $viewsDir;
    }

    /**
     * @inheritdoc
     */
    public function render($template, $params = [])
    {
        $template = preg_match('/\.php$/i', $template) ? $template : $template . '.php';
        extract($params);

        ob_start();

        require $this->viewsDir . '/' . $template;

        return ob_get_clean();
    }

    /**
     * Link renderer. Syntax sugar
     * @param $text
     * @param null $href
     * @param null $link
     * @return string
     */
    protected function link($text, $href = null, $link = null)
    {
        return $link && $link == $href
            ? "<span>{$text}</span>"
            : "<a href='{$href}'>{$text}</a>";
    }

}
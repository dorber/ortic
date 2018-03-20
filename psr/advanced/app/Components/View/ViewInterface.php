<?php

namespace App\Components\View;


interface ViewInterface
{

    /**
     * Renders template with passed params
     * @param $template
     * @param array $params
     * @return mixed
     */
    public function render($template, $params = []);

}
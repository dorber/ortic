<?php

namespace App\Components\View;


interface ViewInterface
{

    public function render($template, $params = []);

}
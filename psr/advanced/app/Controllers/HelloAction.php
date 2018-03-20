<?php

namespace App\Controllers;


use App\Components\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelloAction
{

    public function __invoke(ViewInterface $view, ServerRequestInterface $request, $name)
    {
        return $view->render('hello', ['name' => $name, 'request' => $request]);
    }

}
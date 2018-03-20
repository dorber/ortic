<?php

namespace App\Controllers;


use App\Components\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HelloAction
 * @package App\Controllers
 */
class HelloAction
{

    /**
     * @param ViewInterface $view
     * @param ServerRequestInterface $request
     * @param $name
     * @return mixed
     */
    public function __invoke(ViewInterface $view, ServerRequestInterface $request, $name)
    {
        return $view->render('hello', ['name' => $name, 'request' => $request]);
    }

}
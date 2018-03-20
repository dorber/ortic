<?php

namespace App\Controllers;


use App\Components\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;

class IndexController
{

    /** @var ViewInterface */
    protected $view;


    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }

    public function index(ServerRequestInterface $request)
    {
        return $this->view->render('hello', ['name' => 'World', 'request' => $request]);
    }

    public function counter(ServerRequestInterface $request)
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $newValue = $session->get('counter') + 1;
        $session->set('counter', $newValue);

        return $this->view->render('counter', ['counter' => $newValue, 'request' => $request]);
    }

    public function minus(ServerRequestInterface $request)
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $newValue = $session->get('counter') - 1;
        $session->set('counter', $newValue);

        return $this->view->render('counter', ['counter' => $newValue, 'request' => $request]);
    }

}
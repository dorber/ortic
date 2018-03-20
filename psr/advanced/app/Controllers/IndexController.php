<?php

namespace App\Controllers;


use App\Components\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;

/**
 * Class IndexController
 * @package App\Controllers
 */
class IndexController
{

    /** @var ViewInterface */
    protected $view;


    /**
     * IndexController constructor.
     * @param ViewInterface $view
     */
    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function index(ServerRequestInterface $request)
    {
        return $this->view->render('hello', ['name' => 'World', 'request' => $request]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function counter(ServerRequestInterface $request)
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $newValue = $session->get('counter') + 1;
        $session->set('counter', $newValue);

        return $this->view->render('counter', ['counter' => $newValue, 'request' => $request]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function minus(ServerRequestInterface $request)
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $newValue = $session->get('counter') - 1;
        $session->set('counter', $newValue);

        return $this->view->render('counter', ['counter' => $newValue, 'request' => $request]);
    }

}
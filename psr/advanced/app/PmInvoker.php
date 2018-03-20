<?php

namespace App;


use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class PmMiddleware
 * @package App
 */
class PmInvoker implements ApplicationEnvironmentAwareInterface
{

    /** @var App */
    protected $application;


    /**
     * @param $appenv
     * @param $debug
     */
    public function initialize($appenv, $debug)
    {
        $this->application = require __DIR__ . '/bootstrap.php';
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->application->handle($request);
    }

}
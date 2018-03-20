<?php

namespace App;


use Anonymous\SimpleDi\InvokerInterface;
use function Http\Response\send;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Request;

class App implements RequestHandlerInterface
{

    /** @var ContainerInterface|InvokerInterface */
    protected $container;


    /**
     * App constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->container->call(['dispatcher', 'dispatch'], [$request]);
    }

    /**
     * Runs the dispatcher with the server request
     */
    public function run()
    {
        send($this->handle(Request::createFromGlobals($_SERVER)));
    }

}
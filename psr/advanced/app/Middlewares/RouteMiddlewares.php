<?php

namespace App\Middlewares;


use App\Components\Route\RouteHandler;
use Middleland\Dispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RouteMiddlewares
 * @package App\Middlewares
 */
class RouteMiddlewares implements MiddlewareInterface
{

    /** @var ContainerInterface */
    protected $container;


    /**
     * Route constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Handles routes middlewares
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute('request-handler');

        $middlewares = $requestHandler instanceof RouteHandler
            ? $requestHandler->getMiddlewares()
            : [];

        return $middlewares
            ? (new Dispatcher($middlewares, $this->container))->process($request, $handler)
            : $handler->handle($request);
    }

}
<?php

namespace App\Components\Route;


use Anonymous\SimpleDi\FactoryInterface;
use Anonymous\SimpleDi\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteHandler implements RequestHandlerInterface
{

    /** @var ContainerInterface|InvokerInterface|FactoryInterface */
    protected $container;

    /** @var callable */
    protected $handler;

    /** @var array */
    protected $arguments;

    /** @var array */
    protected $middlewares;


    /**
     * RouteHandler constructor.
     * @param ContainerInterface $container
     * @param $handler
     * @param array $arguments
     * @param array $middlerwares
     */
    public function __construct(ContainerInterface $container, $handler, array $arguments = [], array $middlerwares = [])
    {
        $this->container = $container;
        $this->handler = $handler;
        $this->arguments = $arguments;
        $this->middlewares = $middlerwares;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $arguments = [
            'request' => $request,
            'response' => $this->container->instantiate(ResponseInterface::class),
        ];

        foreach ($this->arguments as $argument) {
            $arguments[$argument] = $request->getAttribute($argument);
        }

        ob_start();
        $response = $this->container->call($this->handler, $arguments);
        $output = ob_get_clean();

        if (!$response instanceof ResponseInterface) {
            $response = $this->container->instantiate(ResponseInterface::class)->write($response);
        }

        if (!empty($output) && $response->getBody()->isWritable()) {
            $response->getBody()->write($output);
        }

        return $response;
    }

    /**
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

}
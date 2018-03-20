<?php

namespace App\Components\Route;


use Anonymous\SimpleDi\FactoryInterface;
use Anonymous\SimpleDi\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RouteHandler
 * @package App\Components\Route
 */
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
     * Handles request and passes it to routes handler
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Default arguments
        $arguments = [
            'request' => $request,
            'response' => $this->container->instantiate(ResponseInterface::class),
        ];

        // Routes arguments
        foreach ($this->arguments as $argument) {
            $arguments[$argument] = $request->getAttribute($argument);
        }

        // Collect echoes
        ob_start();
        $response = $this->container->call($this->handler, $arguments);
        $output = ob_get_clean();

        if (!$response instanceof ResponseInterface) {
            $response = $this->container->instantiate(ResponseInterface::class)->write($response);
        }

        // Write echoes to the end of the response
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
<?php

namespace App\Components\Route;


use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Psr\Container\ContainerInterface;

class RouteCollector extends \FastRoute\RouteCollector
{

    protected $currentGroupMiddlewares = [];
    protected $container;


    public function __construct(ContainerInterface $container, RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->container = $container;

        parent::__construct($routeParser, $dataGenerator);
    }

    public function map($httpMethod, $route, $handler, array $middlewares = [])
    {
        $this->addRoute($httpMethod, $route, $handler, $middlewares);
    }

    public function addRoute($httpMethod, $route, $handler, array $middlewares = [])
    {
        $route = $this->currentGroupPrefix . $route;
        $routeMiddlewares = array_merge($this->currentGroupMiddlewares, $middlewares);

        $routeDatas = $this->routeParser->parse($route);

        $arguments = [];
        foreach ($routeDatas as $routeData) {
            foreach ($routeData as $parts) {
                if (!is_array($parts)) {
                    continue;
                }

                $argument = reset($parts);
                $arguments[$argument] = $argument;
            }
        }

        $routeHandler = new RouteHandler($this->container, $handler, $arguments, $routeMiddlewares);

        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $routeHandler);
            }
        }
    }

    public function group($prefix, callable $callback, array $middlewares = [])
    {
        $this->addGroup($prefix, $callback, $middlewares);
    }

    public function addGroup($prefix, callable $callback, array $middlewares = [])
    {
        $previousMiddlewares = $this->currentGroupMiddlewares;
        $this->currentGroupMiddlewares = array_merge($previousMiddlewares, $middlewares);
        parent::addGroup($prefix, $callback);
        $this->currentGroupMiddlewares = $previousMiddlewares;
    }

    public function get($route, $handler, array $middlewares = [])
    {
        $this->addRoute('GET', $route, $handler, $middlewares);
    }

    public function post($route, $handler, array $middlewares = [])
    {
        $this->addRoute('POST', $route, $handler, $middlewares);
    }

    public function put($route, $handler, array $middlewares = [])
    {
        $this->addRoute('PUT', $route, $handler, $middlewares);
    }

    public function delete($route, $handler, array $middlewares = [])
    {
        $this->addRoute('DELETE', $route, $handler, $middlewares);
    }

    public function patch($route, $handler, array $middlewares = [])
    {
        $this->addRoute('PATCH', $route, $handler, $middlewares);
    }

    public function head($route, $handler, array $middlewares = [])
    {
        $this->addRoute('GET', $route, $handler, $middlewares);
    }

}
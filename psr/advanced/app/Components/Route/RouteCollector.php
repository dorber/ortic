<?php

namespace App\Components\Route;


use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Psr\Container\ContainerInterface;

/**
 * Class RouteCollector
 * @package App\Components\Route
 */
class RouteCollector extends \FastRoute\RouteCollector
{

    protected $currentGroupMiddlewares = [];
    protected $container;


    /**
     * @inheritdoc
     */
    public function __construct(ContainerInterface $container, RouteParser $routeParser, DataGenerator $dataGenerator)
    {
        $this->container = $container;

        parent::__construct($routeParser, $dataGenerator);
    }

    /**
     * Alias for addRoute method
     * @param $httpMethod
     * @param $route
     * @param $handler
     * @param array $middlewares List of middlewares
     */
    public function map($httpMethod, $route, $handler, array $middlewares = [])
    {
        $this->addRoute($httpMethod, $route, $handler, $middlewares);
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function addRoute($httpMethod, $route, $handler, array $middlewares = [])
    {
        // Group prefix
        $route = $this->currentGroupPrefix . $route;

        // Inherit group middlewares
        $routeMiddlewares = array_merge($this->currentGroupMiddlewares, $middlewares);

        $routeDatas = $this->routeParser->parse($route);

        // Collect arguments names to inject them in the future
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

        // Wrap route handler
        $routeHandler = new RouteHandler($this->container, $handler, $arguments, $routeMiddlewares);

        // Add routes for all methods
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $routeHandler);
            }
        }
    }

    /**
     * Alias for addGroup method
     * @param $prefix
     * @param callable $callback
     * @param array $middlewares
     */
    public function group($prefix, callable $callback, array $middlewares = [])
    {
        $this->addGroup($prefix, $callback, $middlewares);
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function addGroup($prefix, callable $callback, array $middlewares = [])
    {
        $previousMiddlewares = $this->currentGroupMiddlewares;
        $this->currentGroupMiddlewares = array_merge($previousMiddlewares, $middlewares);
        parent::addGroup($prefix, $callback);
        $this->currentGroupMiddlewares = $previousMiddlewares;
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function get($route, $handler, array $middlewares = [])
    {
        $this->addRoute('GET', $route, $handler, $middlewares);
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function post($route, $handler, array $middlewares = [])
    {
        $this->addRoute('POST', $route, $handler, $middlewares);
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function put($route, $handler, array $middlewares = [])
    {
        $this->addRoute('PUT', $route, $handler, $middlewares);
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function delete($route, $handler, array $middlewares = [])
    {
        $this->addRoute('DELETE', $route, $handler, $middlewares);
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function patch($route, $handler, array $middlewares = [])
    {
        $this->addRoute('PATCH', $route, $handler, $middlewares);
    }

    /**
     * @inheritdoc
     * @param array $middlewares List of middlewares
     */
    public function head($route, $handler, array $middlewares = [])
    {
        $this->addRoute('GET', $route, $handler, $middlewares);
    }

}
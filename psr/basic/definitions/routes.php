<?php

namespace Routes;

use \Psr\Http\Message\ServerRequestInterface;

function response() { return new \Slim\Http\Response(); }

/**
 * @var \FastRoute\RouteCollector $r
 * @var \Psr\Container\ContainerInterface|\Anonymous\SimpleDi\FactoryInterface $c
 */

$r->get('/', function () use ($c) {
    return $c->instantiate(\Slim\Http\Response::class)->write('Hello, World!');
});

$r->get('/{name:[a-zA-Z0-9_-]+}', function (ServerRequestInterface $request) {
    return response()->write("Hello, {$request->getAttribute('name')}!");
});
<?php

namespace Routes;

use \Psr\Http\Message\ServerRequestInterface;

function response() { return new \Slim\Http\Response(); }

/**
 * @var \FastRoute\RouteCollector $r
 */

$r->get('/', function () {
    return response()->write('Hello, World!');
});

$r->get('/{name:[a-zA-Z0-9_-]+}', function (ServerRequestInterface $request) {
    return response()->write("Hello, {$request->getAttribute('name')}!");
});
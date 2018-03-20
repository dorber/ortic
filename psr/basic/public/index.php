<?php

require_once __DIR__ . '/../vendor/autoload.php';


$container = new \Anonymous\SimpleDi\Container(require __DIR__ . '/../definitions/di.php', true);

$dispatcher = new \Middleland\Dispatcher([
    \Middlewares\ErrorHandler::class,
    \Middlewares\FastRoute::class,
    \Middlewares\RequestHandler::class,
], $container);

\Http\Response\send($container->call(
    [$dispatcher, 'dispatch'],
    [\Slim\Http\Request::createFromGlobals($_SERVER)]
));

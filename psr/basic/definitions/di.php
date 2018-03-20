<?php

use \Psr\Container\ContainerInterface;

return [
    \FastRoute\Dispatcher::class => function (ContainerInterface $c) {
        return FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            require __DIR__ . '/routes.php';
        });
    },
];
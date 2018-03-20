<?php

return [
    \Middlewares\ErrorHandler::class,
    \Middlewares\FastRoute::class,
    \App\Middlewares\RouteMiddlewares::class,
    \Middlewares\RequestHandler::class,
];
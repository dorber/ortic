# Ortic

[Ortic](../../README.md) > [PSR и будущее приложений на PHP](../README.md) > Basic application

[composer.json](composer.json)

```json
{
    "require": {
        "anonymous-php/simple-di": "^1.1",
        "http-interop/response-sender": "^1.0",
        "middlewares/error-handler": "^1.0",
        "middlewares/fast-route": "^1.0",
        "middlewares/request-handler": "^1.1",
        "oscarotero/middleland": "^1.0",
        "slim/http": "^0.3.0"
    }
}
```

[public/index.php](public/index.php)

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new \Anonymous\SimpleDi\Container(
    require __DIR__ . '/../definitions/di.php', true);

$dispatcher = new \Middleland\Dispatcher([
    \Middlewares\ErrorHandler::class,
    \Middlewares\FastRoute::class,
    \Middlewares\RequestHandler::class,
], $container);

\Http\Response\send($container->call(
    [$dispatcher, 'dispatch'],
    [\Slim\Http\Request::createFromGlobals($_SERVER)]
));
```

[definitions/di.php](definitions/di.php)

```php
<?php

use \Psr\Container\ContainerInterface;

return [
    \FastRoute\Dispatcher::class => function (ContainerInterface $c) {
        return FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            require __DIR__ . '/routes.php';
        });
    },
];
```

[definitions/routes.php](definitions/routes.php)

```php
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
```
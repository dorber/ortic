# Ortic

[Ortic](../../README.md) > [PSR и будущее приложений на PHP](../README.md) > Basic application

Перед вами полноценное современное приложение на 30 строк кода. Конечно, это число не учитывает код подключаемых 
библиотек, но и они не столь велики.

Здесь и контейнер зависимостей, и настоящий роутер, и PSR-7 запрос/ответ. Вы можете возразить, что не хватает 
логирования, но и оно добавляется несколькими строками кода (см. [Advanced application](../advanced/README.md)). 

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
        return FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) use ($c) {
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

// Синтаксический сахар, скорее, пример плохого кода в данном случае
function response() { return new \Slim\Http\Response(); }

/**
 * @var \FastRoute\RouteCollector $r
 * @var \Psr\Container\ContainerInterface|\Anonymous\SimpleDi\FactoryInterface $c
 */

$r->get('/', function () use ($c) {
    // Лучше использовать зависимость от интерфейса, не хотелось излишне усложнять текущий пример
    return $c->instantiate(\Slim\Http\Response::class)->write('Hello, World!');
});

$r->get('/{name:[a-zA-Z0-9_-]+}', function (ServerRequestInterface $request) {
    return response()->write("Hello, {$request->getAttribute('name')}!");
});
```

Пытливый читатель отметит, что приведенный на этой странице код немного отличается от того, что представлен 
в репозитории. А именно, здесь пробрасывается переменная `$c`, содержащая указатель на контейнер. Таким образом мы имеем 
возможность получить из контейнера необходимые зависимости без автоматических связывания и инъекции.

Реализация полноценного Dependency Injection разобрана в [Advanced application](../advanced/README.md)
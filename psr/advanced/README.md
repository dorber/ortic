# Ortic

[Ortic](../../README.md) > [PSR и будущее приложений на PHP](../README.md) > Advanced application

Давайте теперь посмотрим, чего не хватает или что мы можем добавить, чтобы улучшить наше приложение:

* Инъекция зависимостей,
* Мидлвары для роутов,
* Сессия,
* Еще больше скорости.

В первую очередь разберем на части наш `index.php`, реализуем простейший класс приложения `App`, инстанцирование 
которого вынесем в отдельный файл [bootstrap.php](app/bootstrap.php):

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new \Anonymous\SimpleDi\Container(require __DIR__ . '/../definitions/di.php', true);

return new \App\App($container);
```

Диспетчер обработки запроса уходит в [файл описания](definitions/di.php) контейнера зависимостей, где получает список 
мидлваров приложения из одноименного файла:

```php
<?php

return [
    'dispatcher' => function (ContainerInterface $c) {
        return new \Middleland\Dispatcher(
            require __DIR__ . '/middleware.php',
            $c
        );
    },
    // ...
];
```

В итоге наш [index.php](public/index.php) прилично похудел:

```php
<?php

(require __DIR__ . '/../app/bootstrap.php')->run();
```

Текущий способ подключения FastRoute не поддерживает инъекцию зависимостей, давайте это исправим. Есть несколько 
вариантов решения проблемы, одним из которых является переопределение метода, который запускает обработчик (`handler`) 
роута, – в нашем случае это мидвар `Middlewares\RequestHandler`. Или мы можем переопределить обработчик роута, решив
заодно вторую поставленную перед нами задачу "Мидлвары для роутов".

Переопределим [RouteCollector](app/Components/Route/RouteCollector.php), расширив его методы таким образом, чтобы они 
принимали еще одним параметром список мидлваров, а при добавлении роута оборачивали его обработчик в нашу обертку:

```php
<?php

    // ...
    public function addRoute($httpMethod, $route, $handler, array $middlewares = [])
    {
        // ...
        $routeHandler = new RouteHandler($this->container, $handler, $arguments, $routeMiddlewares);
        // ...
    }
```

[RouteHandler](app/Components/Route/RouteCollector.php) имеет информацию о привязанных мидлварах и умеет запустить на 
выполнение оригинальный обработчик роута с полноценной инъекцией зависимостей.

Если заглянуть в [файл](definitions/middleware.php) со списком основых мидлваров приложения, можно заметить, здесь 
появился еще один мидлвар `RouteMiddlewares`. Его предназначение в том, чтобы получить список мидлваров для роута и 
обработать их, вернув ответ или передав управление дальше. 

```php
<?php

return [
    \Middlewares\ErrorHandler::class,
    \Middlewares\FastRoute::class,
    \App\Middlewares\RouteMiddlewares::class,
    \Middlewares\RequestHandler::class,
];
```

Таким образом можно производить, например, проверки прав доступа до запуска обработчика роута, который тянет за собой 
инъекцию сервиса с подключением к базе данных. Или как в нашем примере, инициализировать сессию для определенной группы
роутов.

```php
<?php

use App\Controllers\HelloAction;
use App\Controllers\IndexController;
use App\Components\Route\RouteCollector;
use PSR7Sessions\Storageless\Http\SessionMiddleware;

/**
 * @var RouteCollector $r
 */

$r->get('/', [IndexController::class, 'index']);

$r->group('/', function (RouteCollector $r) {
    $r->get('counter', [IndexController::class, 'counter']);
    $r->get('counter/minus', [IndexController::class, 'minus']);
}, [SessionMiddleware::class]);

$r->get('/exception', function () {
    throw new Exception('Test exception');
});
$r->get('/{name:[a-zA-Z0-9_-]+}', HelloAction::class);
```

Раз уж речь зашла о сессиях, стоит упомянуть, в этом приложении используется 
[реализация сессиии](https://github.com/psr7-sessions/storageless) без хранения состояния на сервере. Подход, похожий на 
JWT. Данные подписываются секретным ключем, а в качестве хранилища используются куки на стороне пользователя. Stateless 
архитектура положительно сказывается на возможности горизонтального масштабирования приложения. 

Не так давно вышел первый релиз менеджера процессов [PHP-PM](https://github.com/php-pm/php-pm), который позволяет
вашему приложению один раз инициализировать все подключения и произвести ресурсоемкие операции и далее просто 
обрабатывать поступающие запросы. О подробностях реализации и преимуществах данного подхода я предлагаю вам прочитать на 
странице проекта, а здесь давайте подключим PHP-PM к нашему приложению:

```bash
composer require php-pm/php-pm
```

Все подключение сводится к реализации класса адаптера [PmInvoker](app/PmInvoker.php), в котором инициализируется 
приложение и реализуется метод для запуска обработчика запросов.

```php
<?php

namespace App;


use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class PmMiddleware
 * @package App
 */
class PmInvoker implements ApplicationEnvironmentAwareInterface
{

    /** @var App */
    protected $application;


    /**
     * @param $appenv
     * @param $debug
     */
    public function initialize($appenv, $debug)
    {
        $this->application = require __DIR__ . '/bootstrap.php';
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->application->handle($request);
    }

}
```

Пример сохраненной конфигурации для PHP-PM можно найти в файле [ppm.json](ppm.json):

```json
{
    "bridge": "InvokableMiddleware",
    "host": "0.0.0.0",
    "port": 8080,
    "workers": 2,
    "app-env": "prod",
    "debug": 0,
    "logging": 0,
    "static-directory": "public\/",
    "bootstrap": "App\\PmInvoker",
    "max-requests": 1000,
    "populate-server-var": true,
    "socket-path": "storage\/ppm\/run\/",
    "pidfile": "storage\/ppm\/ppm.pid"
}
``` 

Если ваше приложение не хранит состояние на сервере, вы можете запустить сколь угодно много его экземпляров, обеспечив
таким образом простое и эффективное масштабирование без необходимости обеспечения доступа к хранилищу состояний и 
поддержания его целостности.

При обсуждении [Basic application](../basic/README.md) я обещал рассказать о логировании. Тут все очень просто: 
* Определяем свой `ErrorHandler`, наследующий `Middlewares\ErrorHandlerDefault`,
* Описываем зависимость обработчика ошибок от нашего `ErrorHandler` и реализации логгера
* Разрешаем обработчику перехватывать необработанные исключения
* Логируем ошибки в методе `handle`

```php
<?php

return [
    // ...
    \Middlewares\ErrorHandler::class => function (ContainerInterface $c) {
        $handler = new \Middlewares\ErrorHandler(
            new \App\Components\ErrorHandler($c->get(\Psr\Log\LoggerInterface::class))
        );

        $handler->catchExceptions();

        return $handler;
    },
    // ...
];
```

Свой обработчик ошибок требуется так же в случае, если вы хотите оформить страницы ошибок в дизайне приложения.

А дальше на ваш вкус: сервисы, репозитории, модели, шаблонизаторы.

Таким я вижу будущее приложений на PHP. 
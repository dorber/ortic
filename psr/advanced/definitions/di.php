<?php

use \Psr\Container\ContainerInterface;

return [
    'dispatcher' => function (ContainerInterface $c) {
        return new \Middleland\Dispatcher(
            require __DIR__ . '/middleware.php',
            $c
        );
    },

    \Psr\Http\Message\ResponseInterface::class => \Slim\Http\Response::class,

    \FastRoute\RouteParser::class => \FastRoute\RouteParser\Std::class,
    \FastRoute\DataGenerator::class => \FastRoute\DataGenerator\GroupCountBased::class,
    \FastRoute\Dispatcher::class => function (\Anonymous\SimpleDi\FactoryInterface $c) {
        /** @var \App\Components\Route\RouteCollector $r */
        $r = $c->make(\App\Components\Route\RouteCollector::class);

        require __DIR__ . '/routes.php';

        return new \FastRoute\Dispatcher\GroupCountBased($r->getData());
    },

    \App\Components\View\ViewInterface::class => function () {
        return new App\Components\View\View(__DIR__ . '/../views');
    },

    'session.secretKey' => 'mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw',
    'session.cookieName' => 'sid',
    'session.ttl' => 1200,

    \PSR7Sessions\Storageless\Http\SessionMiddleware::class => function (ContainerInterface $c) {
        $secret = $c->get('session.secretKey');

        return new \PSR7Sessions\Storageless\Http\SessionMiddleware(
            new \Lcobucci\JWT\Signer\Hmac\Sha384(),
            $secret,
            $secret,
            \Dflydev\FigCookies\SetCookie::create($c->get('session.cookieName'))
                ->withHttpOnly(true)
                ->withPath('/'),
            new \Lcobucci\JWT\Parser(),
            $c->get('session.ttl'),
            new \Lcobucci\Clock\SystemClock()
        );
    },

    \Middlewares\ErrorHandler::class => function (ContainerInterface $c) {
        $handler = new \Middlewares\ErrorHandler(
            new \App\Components\ErrorHandler($c->get(\Psr\Log\LoggerInterface::class))
        );

        $handler->catchExceptions();

        return $handler;
    },

    'logger.level' => \Monolog\Logger::DEBUG,
    'logger.path' => __DIR__ . '/../logs/app.log',

    Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
        $handler = new Monolog\Handler\StreamHandler($c->get('logger.path'), $c->get('logger.level'));
        $handler->setFormatter(new \Monolog\Formatter\LineFormatter(null, null, true));

        $logger = new Monolog\Logger('app');
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler($handler);

        return $logger;
    },
];
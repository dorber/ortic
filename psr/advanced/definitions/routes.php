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

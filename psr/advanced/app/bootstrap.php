<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new \Anonymous\SimpleDi\Container(require __DIR__ . '/../definitions/di.php', true);

return new \App\App($container);
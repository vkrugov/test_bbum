<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$container = require dirname(__DIR__) . '/bootstrap.php';

(new \App\Application($container))->run();

<?php

declare(strict_types=1);

return [
    'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('REDIS_PORT') ?: 6379),
    'password' => getenv('REDIS_PASSWORD') ?: '',
    'db' => (int) (getenv('REDIS_DB') ?: 0),
];

<?php

declare(strict_types=1);

return [
    'env' => getenv('APP_ENV') ?: 'production',
    'log_level' => getenv('LOG_LEVEL') ?: 'info',
];

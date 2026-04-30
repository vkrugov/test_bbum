<?php

declare(strict_types=1);

namespace App\Providers;

use App\Container;
use App\Logger\FileLogger;
use App\Providers\Contracts\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $rootDir = dirname(__DIR__, 2);

        $container->bind(LoggerInterface::class, function () use ($rootDir, $config): FileLogger {
            return new FileLogger(
                logPath: $rootDir . '/logs/app.log',
                minLevel: $config['log_level'],
            );
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Container;
use App\Providers\Contracts\ServiceProviderInterface;
use App\Storage\Contracts\StorageInterface;
use App\Storage\RedisStorage;

class StorageServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';

        $container->bind(StorageInterface::class, function () use ($config): RedisStorage {
            return new RedisStorage($config);
        });
    }
}

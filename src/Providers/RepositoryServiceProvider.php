<?php

declare(strict_types=1);

namespace App\Providers;

use App\Container;
use App\Providers\Contracts\ServiceProviderInterface;
use App\Repository\Contracts\TaskRepositoryInterface;
use App\Repository\RedisTaskRepository;
use App\Storage\Contracts\StorageInterface;

class RepositoryServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->bind(TaskRepositoryInterface::class, function (Container $c): TaskRepositoryInterface {
            return new RedisTaskRepository($c->make(StorageInterface::class));
        });
    }
}

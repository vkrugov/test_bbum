<?php

declare(strict_types=1);

use App\Container;
use App\Providers\AppServiceProvider;
use App\Providers\Contracts\ServiceProviderInterface;
use App\Providers\RepositoryServiceProvider;
use App\Providers\StorageServiceProvider;
use App\Support\EnvLoader;

$rootDir = __DIR__;

EnvLoader::load($rootDir . '/.env');

$container = new Container();

$providers = [
    AppServiceProvider::class,
    StorageServiceProvider::class,
    RepositoryServiceProvider::class,
];

/** @var ServiceProviderInterface $providerClass */
foreach ($providers as $providerClass) {
    (new $providerClass())->register($container);
}

return $container;

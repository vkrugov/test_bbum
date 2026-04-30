<?php

declare(strict_types=1);

namespace App\Providers\Contracts;

use App\Container;

interface ServiceProviderInterface
{
    public function register(Container $container): void;
}

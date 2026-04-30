<?php

declare(strict_types=1);

namespace App\Router\Routes;

use App\Container;
use App\Controller\PageController;
use App\Router\Router;

class WebRoutes
{
    public function __construct(private readonly Container $container) {}

    public function register(Router $router): void
    {
        $router->get('/', [PageController::class, 'home']);
        $router->get('/tasks/create', [PageController::class, 'taskCreate']);
        $router->get('/tasks', [PageController::class, 'taskList']);
    }
}

<?php

declare(strict_types=1);

namespace App\Router\Routes;

use App\Container;
use App\Controller\TaskController;
use App\Router\Router;

class ApiRoutes
{
    public function __construct(private readonly Container $container)
    {
    }

    public function register(Router $router): void
    {
        $router->get('/api/tasks', [TaskController::class, 'index']);
        $router->post('/api/tasks', [TaskController::class, 'store']);
        $router->get('/api/tasks/{id}', [TaskController::class, 'show']);
    }
}

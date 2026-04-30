<?php

declare(strict_types=1);

namespace App;

use App\Console\Kernel;
use App\Http\Request;
use App\Router\Router;
use App\Router\Routes\ApiRoutes;
use App\Router\Routes\WebRoutes;
use App\Service\TaskScheduler;

class Application
{
    public function __construct(private readonly Container $container)
    {
        $container->bind(Container::class, fn() => $container);
    }

    public function run(): void
    {
        $router = $this->container->make(Router::class);
        $request = $this->container->make(Request::class);

        $this->container->make(WebRoutes::class)->register($router);
        $this->container->make(ApiRoutes::class)->register($router);

        $router->dispatch($request)->send();
    }

    public function handleCron(): int
    {
        $this->container->make(TaskScheduler::class)->run();

        return 0;
    }

    /**
     * @param string[] $argv
     * @return int  Exit code.
     */
    public function handleCli(array $argv): int
    {
        $commandName = $argv[1] ?? null;
        $args = array_slice($argv, 2);
        $kernel = $this->container->make(Kernel::class);
        $commands = $kernel->resolve();

        if ($commandName === null || !isset($commands[$commandName])) {
            if ($commandName !== null) {
                fwrite(STDERR, "Unknown command: {$commandName}\n\n");
            }

            $kernel->usage();

            return $commandName !== null ? 1 : 0;
        }

        return $this->container->make($commands[$commandName])->handle($args);
    }
}

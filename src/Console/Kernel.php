<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\Contracts\CommandInterface;
use App\Console\Commands\TaskAddCommand;
use App\Console\Commands\TaskListCommand;
use App\Container;

class Kernel
{
    /** @var array<int, class-string<CommandInterface>> */
    private array $commands = [
        TaskAddCommand::class,
        TaskListCommand::class,
    ];

    public function __construct(private readonly Container $container) {}

    /**
     * @return array<string, class-string<CommandInterface>>
     */
    public function resolve(): array
    {
        $map = [];

        foreach ($this->commands as $class) {
            /** @var CommandInterface $instance */
            $instance = $this->container->make($class);
            $map[$instance->getName()] = $class;
        }

        return $map;
    }

    public function usage(): void
    {
        echo "Usage:\n";

        foreach ($this->commands as $class) {
            /** @var CommandInterface $instance */
            $instance = $this->container->make($class);
            echo "  " . $instance->getDescription() . "\n";
        }
    }
}

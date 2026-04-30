<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exception\StorageException;
use App\Service\TaskService;

class TaskAddCommand extends AbstractCommand
{
    public function __construct(private readonly TaskService $taskService) {}

    public function getName(): string
    {
        return 'task:add';
    }

    public function getDescription(): string
    {
        return 'task:add <time_offset> <payload>   Create a new task (+15m, +3h, +1d)';
    }

    /**
     * @param string[] $args  [time_offset, payload]
     * @return int  Exit code.
     */
    public function handle(array $args): int
    {
        if (count($args) < 2) {
            $this->error("Usage: task:add <time_offset> <payload>");
            $this->error("  time_offset: +15m | +3h | +1d");
            return 1;
        }

        [$offset, $payload] = [$args[0], implode(' ', array_slice($args, 1))];

        try {
            $task = $this->taskService->createTask($offset, $payload);
            $this->writeln("Task created: {$task->id}");
            $this->writeln("  Payload:      {$task->payload}");
            $this->writeln("  Scheduled at: " . date('Y-m-d H:i:s', $task->scheduledAt));
            return 0;
        } catch (\InvalidArgumentException $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        } catch (StorageException $e) {
            $this->error("Storage error: " . $e->getMessage());
            return 1;
        }
    }
}

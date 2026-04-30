<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exception\StorageException;
use App\Model\Task;
use App\Enum\TaskStatus;
use App\Service\TaskService;

class TaskListCommand extends AbstractCommand
{
    public function __construct(private readonly TaskService $taskService) {}

    public function getName(): string
    {
        return 'task:list';
    }

    public function getDescription(): string
    {
        return 'task:list [--status=<status>]       List tasks (pending|running|done|error)';
    }

    /**
     * @param string[] $args  Optional: [--status=pending|running|done|error]
     * @return int  Exit code.
     */
    public function handle(array $args): int
    {
        $status = null;

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--status=')) {
                $value = substr($arg, 9);
                $status = TaskStatus::tryFrom($value);
                if ($status === null) {
                    $this->error("Invalid status: {$value}");
                    return 1;
                }
            }
        }

        $filters = $status !== null ? ['status' => $status] : [];

        try {
            $result = $this->taskService->listTasks($filters);
        } catch (StorageException $e) {
            $this->error("Storage error: " . $e->getMessage());
            return 1;
        }

        $tasks = $result['tasks'];

        if ($tasks === []) {
            $this->writeln("No tasks found.");
            return 0;
        }

        $this->renderTable($tasks);
        return 0;
    }

    /**
     * @param Task[] $tasks
     */
    private function renderTable(array $tasks): void
    {
        $this->writeln(sprintf("%-36s  %-8s  %-19s  %s", 'ID', 'STATUS', 'SCHEDULED AT', 'PAYLOAD'));
        $this->writeln(str_repeat('-', 100));

        foreach ($tasks as $task) {
            $scheduledAt = date('Y-m-d H:i:s', $task->scheduledAt);
            $payload = strlen($task->payload) > 35 ? substr($task->payload, 0, 32) . '...' : $task->payload;
            $this->writeln(sprintf("%-36s  %-8s  %-19s  %s", $task->id, $task->status->value, $scheduledAt, $payload));

            if ($task->errorMessage !== null) {
                $this->writeln("  Error: {$task->errorMessage}");
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\StorageException;
use App\Exception\TaskNotFoundException;
use App\Enum\TaskStatus;
use App\Model\Task;
use App\Repository\Contracts\TaskRepositoryInterface;
use App\Support\Uuid;
use Psr\Log\LoggerInterface;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
        private readonly LoggerInterface         $logger,
    )
    {
    }

    /**
     * @param string $offsetStr e.g. +15m, +3h, +1d
     * @param string $payload
     * @return Task
     * @throws \InvalidArgumentException
     * @throws StorageException
     */
    public function createTask(string $offsetStr, string $payload): Task
    {
        $scheduledAt = $this->parseOffset($offsetStr);

        $task = new Task(
            id: Uuid::generate(),
            payload: $payload,
            status: TaskStatus::PENDING,
            scheduledAt: $scheduledAt,
            createdAt: time(),
            updatedAt: time(),
        );

        $this->repository->save($task);
        $this->logger->info('Task created', ['id' => $task->id, 'scheduled_at' => $scheduledAt]);

        return $task;
    }

    /**
     * @throws TaskNotFoundException
     * @throws StorageException
     */
    public function getTask(string $id): Task
    {
        return $this->repository->findById($id);
    }

    /**
     * @return array{tasks: Task[], total: int, page: int, per_page: int}
     * @throws StorageException
     */
    public function listTasks(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        return [
            'tasks' => $this->repository->findAll($filters, $offset, $perPage),
            'total' => $this->repository->countAll($filters),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function parseOffset(string $offset): int
    {
        if (!preg_match('/^\+(\d+)(m|h|d)$/', $offset, $matches)) {
            throw new \InvalidArgumentException(
                "Invalid offset format [{$offset}]. Expected: +15m, +3h, +1d"
            );
        }

        $value = (int)$matches[1];
        $seconds = match ($matches[2]) {
            'm' => $value * 60,
            'h' => $value * 3600,
            'd' => $value * 86400,
        };

        return (int)(floor((time() + $seconds) / 60) * 60);
    }
}

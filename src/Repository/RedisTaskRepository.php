<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\StorageException;
use App\Exception\TaskNotFoundException;
use App\Model\Task;
use App\Enum\TaskStatus;
use App\Repository\Contracts\TaskRepositoryInterface;
use App\Storage\Contracts\StorageInterface;

class RedisTaskRepository implements TaskRepositoryInterface
{
    private const KEY_PREFIX = 'task:';

    public function __construct(private readonly StorageInterface $storage)
    {
    }

    /**
     * @throws StorageException
     */
    public function save(Task $task): void
    {
        $json = json_encode($task->toArray(), JSON_THROW_ON_ERROR);
        $this->storage->set(self::KEY_PREFIX . $task->id, $json);
    }

    /**
     * @throws TaskNotFoundException
     * @throws StorageException
     */
    public function findById(string $id): Task
    {
        $json = $this->storage->get(self::KEY_PREFIX . $id);

        if ($json === null) {
            throw new TaskNotFoundException("Task not found: {$id}");
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return Task::fromArray($data);
    }

    /**
     * @return Task[]
     * @throws StorageException
     */
    public function findAll(array $filters = [], int $offset = 0, int $limit = 0): array
    {
        $tasks = $this->applyFilters($this->fetchByKeys(self::KEY_PREFIX . '*'), $filters);

        if ($limit > 0) {
            return array_values(array_slice($tasks, $offset, $limit));
        }

        return $offset > 0 ? array_values(array_slice($tasks, $offset)) : $tasks;
    }

    /**
     * @throws StorageException
     */
    public function countAll(array $filters = []): int
    {
        return count($this->applyFilters($this->fetchByKeys(self::KEY_PREFIX . '*'), $filters));
    }

    /**
     * @return Task[]
     * @throws StorageException
     */
    public function findByStatus(TaskStatus $status): array
    {
        return $this->findAll(['status' => $status]);
    }

    private function applyFilters(array $tasks, array $filters): array
    {
        if (isset($filters['status']) && $filters['status'] instanceof TaskStatus) {
            $tasks = array_values(array_filter(
                $tasks,
                fn(Task $t) => $t->status === $filters['status'],
            ));
        }

        return $tasks;
    }

    /**
     * @return Task[]
     * @throws StorageException
     */
    public function findDue(): array
    {
        $now = time();

        return array_values(
            array_filter(
                $this->findByStatus(TaskStatus::PENDING),
                fn(Task $t) => $t->scheduledAt <= $now,
            )
        );
    }

    /**
     * @return Task[]
     * @throws StorageException
     */
    private function fetchByKeys(string $pattern): array
    {
        $keys = $this->storage->keys($pattern);
        $tasks = [];

        foreach ($keys as $key) {
            $json = $this->storage->get($key);
            if ($json === null) {
                continue;
            }
            /** @var array<string, mixed> $data */
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $tasks[] = Task::fromArray($data);
        }

        usort($tasks, fn(Task $a, Task $b) => $a->scheduledAt <=> $b->scheduledAt);

        return $tasks;
    }
}

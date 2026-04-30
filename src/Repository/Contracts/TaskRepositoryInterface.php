<?php

declare(strict_types=1);

namespace App\Repository\Contracts;

use App\Exception\StorageException;
use App\Exception\TaskNotFoundException;
use App\Model\Task;
use App\Enum\TaskStatus;

interface TaskRepositoryInterface
{
    /**
     * @throws StorageException
     */
    public function save(Task $task): void;

    /**
     * @throws TaskNotFoundException
     * @throws StorageException
     */
    public function findById(string $id): Task;

    /**
     * @return Task[]
     * @throws StorageException
     */
    public function findAll(array $filters = [], int $offset = 0, int $limit = 0): array;

    /**
     * @throws StorageException
     */
    public function countAll(array $filters = []): int;

    /**
     * @return Task[]
     * @throws StorageException
     */
    public function findByStatus(TaskStatus $status): array;

    /**
     * @return Task[]
     * @throws StorageException
     */
    public function findDue(): array;
}

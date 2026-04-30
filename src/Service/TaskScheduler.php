<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\StorageException;
use App\Model\Task;
use App\Enum\TaskStatus;
use App\Repository\Contracts\TaskRepositoryInterface;
use Psr\Log\LoggerInterface;

class TaskScheduler
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @throws StorageException
     */
    public function run(): void
    {
        $tasks = $this->repository->findDue();
        $this->logger->info('Scheduler run', ['due_count' => count($tasks)]);

        foreach ($tasks as $task) {
            $this->process($task);
        }
    }

    private function process(Task $task): void
    {
        $task->status = TaskStatus::RUNNING;
        $task->updatedAt = time();

        try {
            $this->repository->save($task);
        } catch (StorageException $e) {
            $this->logger->error('Failed to mark task as running', ['id' => $task->id, 'error' => $e->getMessage()]);

            return;
        }

        $this->logger->info('Executing task', ['id' => $task->id, 'payload' => $task->payload]);

        try {
            $this->execute($task);
            $task->status = TaskStatus::DONE;
            $task->updatedAt = time();
            $this->logger->info('Task completed', ['id' => $task->id]);
        } catch (\Throwable $e) {
            $task->status = TaskStatus::ERROR;
            $task->errorMessage = $e->getMessage();
            $task->updatedAt = time();
            $this->logger->error('Task failed', ['id' => $task->id, 'error' => $e->getMessage()]);
        }

        try {
            $this->repository->save($task);
        } catch (StorageException $e) {
            $this->logger->error('Failed to save task result', ['id' => $task->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function execute(Task $task): void
    {
        // Evaluate the payload as a shell command.
        $output = shell_exec($task->payload);
        $this->logger->debug('Task output', ['id' => $task->id, 'output' => $output]);
    }
}

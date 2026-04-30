<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Enum\TaskStatus;
use App\Exception\StorageException;
use App\Model\Task;
use App\Repository\Contracts\TaskRepositoryInterface;
use App\Service\TaskScheduler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TaskSchedulerTest extends TestCase
{
    private TaskRepositoryInterface&MockObject $repository;
    private LoggerInterface&MockObject $logger;
    private TaskScheduler $scheduler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TaskRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->scheduler = new TaskScheduler($this->repository, $this->logger);
    }

    public function testRunWithNoTasksCallsFindDueOnce(): void
    {
        $this->repository->expects($this->once())
            ->method('findDue')
            ->willReturn([]);

        $this->repository->expects($this->never())->method('save');

        $this->scheduler->run();
    }

    public function testRunHappyPathSavesRunningThenDone(): void
    {
        $task = $this->makeTask('task-1', 'true');

        $this->repository->method('findDue')->willReturn([$task]);

        $savedStatuses = [];
        $this->repository->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function (Task $t) use (&$savedStatuses): void {
                $savedStatuses[] = $t->status;
            });

        $this->scheduler->run();

        $this->assertSame(TaskStatus::RUNNING, $savedStatuses[0]);
        $this->assertSame(TaskStatus::DONE, $savedStatuses[1]);
    }

    public function testRunSetsStatusToDoneAfterExecution(): void
    {
        $task = $this->makeTask('task-2', 'true');

        $this->repository->method('findDue')->willReturn([$task]);
        $this->repository->method('save');

        $this->scheduler->run();

        $this->assertSame(TaskStatus::DONE, $task->status);
        $this->assertNull($task->errorMessage);
    }

    public function testRunAbandonsTaskWhenFirstSaveThrows(): void
    {
        $task = $this->makeTask('task-3', 'true');

        $this->repository->method('findDue')->willReturn([$task]);
        $this->repository->expects($this->once())
            ->method('save')
            ->willThrowException(new StorageException('Redis error'));

        // Should not throw; scheduler logs and returns
        $this->scheduler->run();

        // Status was set to RUNNING before save failed — it stays RUNNING locally
        $this->assertSame(TaskStatus::RUNNING, $task->status);
    }

    public function testRunProcessesMultipleTasks(): void
    {
        $task1 = $this->makeTask('t1', 'true');
        $task2 = $this->makeTask('t2', 'true');

        $this->repository->method('findDue')->willReturn([$task1, $task2]);

        $saveCount = 0;
        $this->repository->method('save')
            ->willReturnCallback(function () use (&$saveCount): void {
                $saveCount++;
            });

        $this->scheduler->run();

        $this->assertSame(4, $saveCount); // 2 saves per task
        $this->assertSame(TaskStatus::DONE, $task1->status);
        $this->assertSame(TaskStatus::DONE, $task2->status);
    }

    public function testRunContinuesWithRemainingTasksAfterFirstSaveFails(): void
    {
        $task1 = $this->makeTask('t1', 'true');
        $task2 = $this->makeTask('t2', 'true');

        $this->repository->method('findDue')->willReturn([$task1, $task2]);

        $callCount = 0;
        $this->repository->method('save')
            ->willReturnCallback(function (Task $t) use (&$callCount): void {
                $callCount++;
                if ($callCount === 1) {
                    throw new StorageException('Redis error on first save');
                }
            });

        $this->scheduler->run();

        $this->assertSame(TaskStatus::RUNNING, $task1->status); // abandoned after first save failed
        $this->assertSame(TaskStatus::DONE, $task2->status);   // second task completed
    }

    private function makeTask(string $id, string $payload): Task
    {
        return new Task(
            id: $id,
            payload: $payload,
            status: TaskStatus::PENDING,
            scheduledAt: time() - 60,
            createdAt: time() - 120,
            updatedAt: time() - 120,
        );
    }
}

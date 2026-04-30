<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use App\Enum\TaskStatus;
use App\Exception\StorageException;
use App\Exception\TaskNotFoundException;
use App\Model\Task;
use App\Repository\Contracts\TaskRepositoryInterface;
use App\Service\TaskService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface&MockObject $repository;
    private LoggerInterface&MockObject $logger;
    private TaskService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TaskRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new TaskService($this->repository, $this->logger);
    }

    public function testCreateTaskSavesAndReturnsPendingTask(): void
    {
        $this->repository->expects($this->once())->method('save');

        $task = $this->service->createTask('+15m', 'echo hello');

        $this->assertSame(TaskStatus::PENDING, $task->status);
        $this->assertSame('echo hello', $task->payload);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $task->id,
        );
    }

    public function testCreateTaskScheduledAtIsRoundedToMinute(): void
    {
        $this->repository->method('save');

        $before = (int) (floor((time() + 15 * 60) / 60) * 60);
        $task = $this->service->createTask('+15m', 'cmd');
        $after = (int) (floor((time() + 15 * 60) / 60) * 60);

        $this->assertGreaterThanOrEqual($before, $task->scheduledAt);
        $this->assertLessThanOrEqual($after, $task->scheduledAt);
        $this->assertSame(0, $task->scheduledAt % 60);
    }

    public function testCreateTaskSupportsHourOffset(): void
    {
        $this->repository->method('save');

        $task = $this->service->createTask('+3h', 'cmd');
        $expected = (int) (floor((time() + 3 * 3600) / 60) * 60);

        $this->assertEqualsWithDelta($expected, $task->scheduledAt, 60);
    }

    public function testCreateTaskSupportsDayOffset(): void
    {
        $this->repository->method('save');

        $task = $this->service->createTask('+1d', 'cmd');
        $expected = (int) (floor((time() + 86400) / 60) * 60);

        $this->assertEqualsWithDelta($expected, $task->scheduledAt, 60);
    }

    public function testCreateTaskThrowsOnInvalidOffsetFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createTask('15m', 'cmd');
    }

    public function testCreateTaskThrowsOnUnknownOffsetUnit(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createTask('+5s', 'cmd');
    }

    public function testGetTaskDelegatesToRepository(): void
    {
        $task = $this->makeTask();
        $this->repository->expects($this->once())
            ->method('findById')
            ->with('some-id')
            ->willReturn($task);

        $result = $this->service->getTask('some-id');

        $this->assertSame($task, $result);
    }

    public function testGetTaskPropagatesNotFoundException(): void
    {
        $this->repository->method('findById')
            ->willThrowException(new TaskNotFoundException());

        $this->expectException(TaskNotFoundException::class);

        $this->service->getTask('missing');
    }

    public function testListTasksWithoutFiltersCallsFindAll(): void
    {
        $tasks = [$this->makeTask()];
        $this->repository->expects($this->once())
            ->method('findAll')
            ->with([], 0, 20)
            ->willReturn($tasks);
        $this->repository->method('countAll')
            ->with([])
            ->willReturn(count($tasks));

        $result = $this->service->listTasks();

        $this->assertSame($tasks, $result['tasks']);
        $this->assertSame(count($tasks), $result['total']);
        $this->assertSame(1, $result['page']);
        $this->assertSame(20, $result['per_page']);
    }

    public function testListTasksWithStatusFilterCallsFindAll(): void
    {
        $tasks   = [$this->makeTask()];
        $filters = ['status' => TaskStatus::PENDING];

        $this->repository->expects($this->once())
            ->method('findAll')
            ->with($filters, 0, 20)
            ->willReturn($tasks);
        $this->repository->method('countAll')
            ->with($filters)
            ->willReturn(count($tasks));

        $result = $this->service->listTasks($filters);

        $this->assertSame($tasks, $result['tasks']);
    }

    public function testListTasksPaginationOffsetIsCalculatedFromPage(): void
    {
        $tasks = [$this->makeTask()];
        $this->repository->expects($this->once())
            ->method('findAll')
            ->with([], 40, 20)
            ->willReturn($tasks);
        $this->repository->method('countAll')->willReturn(100);

        $result = $this->service->listTasks([], page: 3, perPage: 20);

        $this->assertSame(3, $result['page']);
        $this->assertSame(100, $result['total']);
    }

    public function testListTasksPropagatesStorageException(): void
    {
        $this->repository->method('findAll')
            ->willThrowException(new StorageException('Redis down'));

        $this->expectException(StorageException::class);

        $this->service->listTasks();
    }

    private function makeTask(): Task
    {
        return new Task(
            id: 'some-id',
            payload: 'echo test',
            status: TaskStatus::PENDING,
            scheduledAt: time() + 900,
            createdAt: time(),
            updatedAt: time(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Model;

use App\Enum\TaskStatus;
use App\Model\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    private function makeTask(): Task
    {
        return new Task(
            id: 'abc-123',
            payload: 'echo hello',
            status: TaskStatus::PENDING,
            scheduledAt: 1700000000,
            createdAt: 1699999000,
            updatedAt: 1699999000,
            errorMessage: null,
        );
    }

    public function testToArrayReturnsExpectedKeys(): void
    {
        $task = $this->makeTask();
        $arr = $task->toArray();

        $this->assertSame('abc-123', $arr['id']);
        $this->assertSame('echo hello', $arr['payload']);
        $this->assertSame('pending', $arr['status']);
        $this->assertSame(1700000000, $arr['scheduled_at']);
        $this->assertSame(1699999000, $arr['created_at']);
        $this->assertSame(1699999000, $arr['updated_at']);
        $this->assertNull($arr['error_message']);
    }

    public function testFromArrayRoundtrip(): void
    {
        $task = $this->makeTask();
        $restored = Task::fromArray($task->toArray());

        $this->assertSame($task->id, $restored->id);
        $this->assertSame($task->payload, $restored->payload);
        $this->assertSame($task->status, $restored->status);
        $this->assertSame($task->scheduledAt, $restored->scheduledAt);
        $this->assertSame($task->createdAt, $restored->createdAt);
        $this->assertSame($task->updatedAt, $restored->updatedAt);
        $this->assertNull($restored->errorMessage);
    }

    public function testFromArrayWithErrorMessage(): void
    {
        $task = Task::fromArray([
            'id' => 'xyz',
            'payload' => 'ls',
            'status' => 'error',
            'scheduled_at' => 1000,
            'created_at' => 900,
            'updated_at' => 950,
            'error_message' => 'command failed',
        ]);

        $this->assertSame(TaskStatus::ERROR, $task->status);
        $this->assertSame('command failed', $task->errorMessage);
    }

    public function testStatusIsMutable(): void
    {
        $task = $this->makeTask();
        $task->status = TaskStatus::DONE;

        $this->assertSame(TaskStatus::DONE, $task->status);
    }
}

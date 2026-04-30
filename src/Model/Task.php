<?php

declare(strict_types=1);

namespace App\Model;

use App\Enum\TaskStatus;

class Task
{
    public function __construct(
        public readonly string $id,
        public readonly string $payload,
        public TaskStatus $status,
        public readonly int $scheduledAt,
        public readonly int $createdAt,
        public int $updatedAt,
        public ?string $errorMessage = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'payload' => $this->payload,
            'status' => $this->status->value,
            'scheduled_at' => $this->scheduledAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'error_message' => $this->errorMessage,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            payload: $data['payload'],
            status: TaskStatus::from($data['status']),
            scheduledAt: (int) $data['scheduled_at'],
            createdAt: (int) $data['created_at'],
            updatedAt: (int) $data['updated_at'],
            errorMessage: $data['error_message'] ?? null,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case DONE = 'done';
    case ERROR = 'error';
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\StorageException;
use App\Http\Request;
use App\Http\Response;
use App\Enum\TaskStatus;
use App\Service\TaskService;

class PageController extends AbstractController
{
    public function __construct(private readonly TaskService $taskService) {}

    public function home(Request $request): Response
    {
        try {
            $stats = [
                'pending' => $this->taskService->listTasks(['status' => TaskStatus::PENDING])['total'],
                'running' => $this->taskService->listTasks(['status' => TaskStatus::RUNNING])['total'],
                'done'    => $this->taskService->listTasks(['status' => TaskStatus::DONE])['total'],
                'error'   => $this->taskService->listTasks(['status' => TaskStatus::ERROR])['total'],
            ];
        } catch (StorageException) {
            $stats = ['pending' => 0, 'running' => 0, 'done' => 0, 'error' => 0];
        }

        return $this->view('home', compact('stats'));
    }

    public function taskCreate(Request $request): Response
    {
        return $this->view('tasks/create');
    }

    public function taskList(Request $request): Response
    {
        return $this->view('tasks/list');
    }
}

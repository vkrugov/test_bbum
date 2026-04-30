<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\StorageException;
use App\Exception\TaskNotFoundException;
use App\Http\Request;
use App\Http\Response;
use App\Enum\TaskStatus;
use App\Service\TaskService;
use Psr\Log\LoggerInterface;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService     $taskService,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function index(Request $request): Response
    {
        $filters = [];

        $statusParam = $request->query['status'] ?? null;
        if ($statusParam !== null) {
            $status = TaskStatus::tryFrom($statusParam);
            if ($status === null) {
                return $this->json(['error' => 'Invalid status value'], 400);
            }
            $filters['status'] = $status;
        }

        $page = (int)($request->query['page'] ?? 1);
        $perPage = (int)($request->query['per_page'] ?? 20);

        if ($page < 1 || $perPage < 1 || $perPage > 100) {
            return $this->json(['error' => 'Invalid pagination parameters'], 400);
        }

        try {
            $result = $this->taskService->listTasks($filters, $page, $perPage);
            $totalPages = $result['total'] > 0 ? (int)ceil($result['total'] / $result['per_page']) : 0;

            return $this->json([
                'data' => array_map(fn($t) => $t->toArray(), $result['tasks']),
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'per_page' => $result['per_page'],
                    'total_pages' => $totalPages,
                ],
            ]);
        } catch (StorageException $e) {
            $this->logger->error('Failed to list tasks', ['error' => $e->getMessage()]);

            return $this->json(['error' => 'Storage error'], 500);
        }
    }

    public function store(Request $request): Response
    {
        $offset = $request->body['offset'] ?? null;
        $payload = $request->body['payload'] ?? null;

        if (!is_string($offset) || !is_string($payload) || $payload === '') {
            return $this->json(['error' => 'Fields offset and payload are required'], 400);
        }

        try {
            $task = $this->taskService->createTask($offset, $payload);

            return $this->json(['data' => $task->toArray()], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (StorageException $e) {
            $this->logger->error('Failed to create task', ['error' => $e->getMessage()]);

            return $this->json(['error' => 'Storage error'], 500);
        }
    }

    public function show(Request $request): Response
    {
        $id = $request->getRouteParam('id', '');

        try {
            $task = $this->taskService->getTask($id);

            return $this->json(['data' => $task->toArray()]);
        } catch (TaskNotFoundException) {
            return $this->json(['error' => 'Task not found'], 404);
        } catch (StorageException $e) {
            $this->logger->error('Failed to fetch task', ['id' => $id, 'error' => $e->getMessage()]);

            return $this->json(['error' => 'Storage error'], 500);
        }
    }
}

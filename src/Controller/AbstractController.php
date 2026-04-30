<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Response;

abstract class AbstractController
{
    protected function json(mixed $data, int $status = 200): Response
    {
        return (new Response())
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function view(string $template, array $data = []): Response
    {
        $viewsDir = dirname(__DIR__, 2) . '/views';
        extract($data);

        ob_start();
        include $viewsDir . '/' . $template . '.php';
        $content = (string) ob_get_clean();

        ob_start();
        include $viewsDir . '/layout.php';

        return (new Response())->html((string) ob_get_clean());
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return (new Response())
            ->withStatus($status)
            ->withHeader('Location', $url);
    }
}

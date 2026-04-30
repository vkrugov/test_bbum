<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    public readonly string $method;
    public readonly string $uri;
    /** @var array<string, string> */
    public readonly array $query;
    /** @var array<string, mixed> */
    public readonly array $body;
    /** @var array<string, string> */
    public readonly array $headers;
    /** @var array<string, string> */
    private array $routeParams = [];

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->headers = $this->parseHeaders();
    }

    /**
     * @param array<string, string> $params
     */
    public function withRouteParams(array $params): static
    {
        $clone = clone $this;
        $clone->routeParams = $params;

        return $clone;
    }

    /**
     * @return array<string, string>
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getRouteParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            if ($raw === false || $raw === '') {
                return [];
            }
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    /**
     * @return array<string, string>
     */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($name)] = (string) $value;
            }
        }

        return $headers;
    }
}

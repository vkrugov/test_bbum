<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    private int $statusCode = 200;
    private string $body = '';
    /** @var array<string, string> */
    private array $headers = [];

    public function withStatus(int $code): self
    {
        $clone = clone $this;
        $clone->statusCode = $code;

        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function withBody(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function json(array $data, int $status = 200): self
    {
        return $this
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    public function html(string $content, int $status = 200): self
    {
        return $this
            ->withStatus($status)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8')
            ->withBody($content);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }
}

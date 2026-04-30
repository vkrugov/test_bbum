<?php

declare(strict_types=1);

namespace App\Storage;

use App\Exception\StorageException;
use App\Storage\Contracts\StorageInterface;
use Predis\Client;
use Predis\PredisException;

class RedisStorage implements StorageInterface
{
    private Client $client;

    /**
     * @param array<string, mixed> $config
     * @throws StorageException
     */
    public function __construct(array $config)
    {
        try {
            $this->client = new Client([
                'scheme' => 'tcp',
                'host' => $config['host'] ?? '127.0.0.1',
                'port' => (int) ($config['port'] ?? 6379),
                'password' => $config['password'] ?: null,
                'database' => (int) ($config['db'] ?? 0),
            ]);
            $this->client->ping();
        } catch (PredisException $e) {
            throw new StorageException('Redis connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws StorageException
     */
    public function get(string $key): ?string
    {
        try {
            $value = $this->client->get($key);

            return $value !== null ? (string) $value : null;
        } catch (PredisException $e) {
            throw new StorageException('Redis get failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws StorageException
     */
    public function set(string $key, string $value): void
    {
        try {
            $this->client->set($key, $value);
        } catch (PredisException $e) {
            throw new StorageException('Redis set failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws StorageException
     */
    public function delete(string $key): void
    {
        try {
            $this->client->del([$key]);
        } catch (PredisException $e) {
            throw new StorageException('Redis delete failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @return string[]
     * @throws StorageException
     */
    public function keys(string $pattern): array
    {
        try {
            return $this->client->keys($pattern) ?? [];
        } catch (PredisException $e) {
            throw new StorageException('Redis keys failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws StorageException
     */
    public function exists(string $key): bool
    {
        try {
            return (bool) $this->client->exists($key);
        } catch (PredisException $e) {
            throw new StorageException('Redis exists failed: ' . $e->getMessage(), 0, $e);
        }
    }
}

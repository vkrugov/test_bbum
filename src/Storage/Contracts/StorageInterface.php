<?php

declare(strict_types=1);

namespace App\Storage\Contracts;

use App\Exception\StorageException;

interface StorageInterface
{
    /**
     * @throws StorageException
     */
    public function get(string $key): ?string;

    /**
     * @throws StorageException
     */
    public function set(string $key, string $value): void;

    /**
     * @throws StorageException
     */
    public function delete(string $key): void;

    /**
     * @return string[]
     * @throws StorageException
     */
    public function keys(string $pattern): array;

    /**
     * @throws StorageException
     */
    public function exists(string $key): bool;
}

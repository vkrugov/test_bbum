<?php

declare(strict_types=1);

namespace App\Console\Commands\Contracts;

interface CommandInterface
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * @param string[] $args
     * @return int  Exit code.
     */
    public function handle(array $args): int;
}

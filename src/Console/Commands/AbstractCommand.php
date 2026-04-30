<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Contracts\CommandInterface;

abstract class AbstractCommand implements CommandInterface
{
    protected function writeln(string $line): void
    {
        echo $line . "\n";
    }

    protected function error(string $line): void
    {
        fwrite(STDERR, $line . "\n");
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Uuid;
use PHPUnit\Framework\TestCase;

class UuidTest extends TestCase
{
    public function testGenerateReturnsValidUuidV4Format(): void
    {
        $uuid = Uuid::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid,
        );
    }

    public function testGenerateReturnsUniqueValues(): void
    {
        $uuids = array_map(fn() => Uuid::generate(), range(1, 100));

        $this->assertCount(100, array_unique($uuids));
    }
}

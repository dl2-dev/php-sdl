<?php declare(strict_types=1);

namespace DL2\SDL\Tests;

use DL2\SDL\Composer;
use DL2\SDL\Json;
use DL2\SDL\Tests\Support\JsonSingletonEmptyFilename;
use LogicException;

/**
 * @internal
 * @covers \DL2\SDL\Json
 * @covers \DL2\SDL\JsonSingleton
 */
final class JsonSingletonTest extends TestCase
{
    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Json::class, Composer::getInstance());
    }

    public function testCtor(): void
    {
        $this->expectException(LogicException::class);
        JsonSingletonEmptyFilename::getInstance();
    }
}

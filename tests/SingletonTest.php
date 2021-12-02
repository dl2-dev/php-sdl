<?php declare(strict_types=1);

namespace DL2\SDL\Tests;

use DL2\SDL\Tests\Support\Singleton;

/**
 * @internal
 * @covers \DL2\SDL\Singleton
 */
final class SingletonTest extends TestCase
{
    public function testCtor(): void
    {
        $this->expectError();
        new Singleton();
    }

    public function testGetInstance(): void
    {
        $one = Singleton::getInstance('one');
        $two = Singleton::getInstance('two');
        static::assertSame('one', $two->arg0);

        $one->arg0 = 'every change must reflect across any copy';
        static::assertSame($one, $two);
    }
}

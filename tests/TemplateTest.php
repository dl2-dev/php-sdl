<?php declare(strict_types=1);

namespace DL2\SPL\Tests;

use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class TemplateTest extends TestCase
{
    /**
     * @template TProviderType as array<int,int>
     *
     * @internal
     *
     * @psalm-return Generator<int, array{0: 1|2|3|4|5, 1: 2|3|4|5|6}, mixed, void>
     */
    public function dataProviderForSomething(): Generator
    {
        yield [1, 2];
        yield [2, 3];
        yield [3, 4];
        yield [4, 5];
        yield [5, 6];
    }

    /**
     * @dataProvider dataProviderForSomething
     */
    public function testSomething(int $input, int $expected): void
    {
        static::assertSame($input + 1, $expected);
    }
}

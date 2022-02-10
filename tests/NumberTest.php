<?php declare(strict_types=1);

namespace DL2\SDL\Tests;

use ArithmeticError;
use DL2\SDL\Number;
use Generator;

/**
 * @internal
 * @covers \DL2\SDL\Number
 */
final class NumberTest extends TestCase
{
    private const ARITHMETIC_ERROR_MESSAGE = '/valid arithmetic expression/i';

    public function dataProviderCtorPositiveNumber(): Generator
    {
        // keep all tests evaluating to '100'
        yield ['(18.78 + 25 + 6.22) * 2'];
        yield ['10 * (2 + 8)'];
        yield ['10 * 2 + 80'];
        yield ['10 ** 2'];
        yield ['10 ^ 2'];
        yield ['100'];
        yield [100.0];
        yield [100];
        yield [new Number(100)];
    }

    /**
     * @dataProvider dataProviderCtorPositiveNumber
     */
    public function testArithmeticBasics(int|float|Number|string $input): void
    {
        $test = (new Number($input, 2))
            ->add(10) // 110
            ->mul(18) // 1980
            ->sub(80) // 1900
            ->div(10) // 190
            ->sub('50%') // 95
            ->add(5) // back to 100
            ->sqrt() // 10
            ->pow(2) // and back to 100 again
            ->sub(20, true) // go to -80
            ->mul(-1)
            ->add(20) // 100 again...
            ->mod(3) // 1
        ;

        static::assertSame('1', $test->intval(false));
        static::assertSame('1.00', $test->floatval(false));

        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessageMatches('/square.+negative number/i');
        $test->mul(-1)->sqrt();
    }

    public function testCtor0(): void
    {
        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessageMatches(self::ARITHMETIC_ERROR_MESSAGE);
        new Number('10 ++ 25'); // NOSONAR
    }

    public function testCtor1(): void
    {
        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessageMatches(self::ARITHMETIC_ERROR_MESSAGE);
        new Number('10 + (10 v 25)'); // NOSONAR
    }

    public function testDivisionByZero(): void
    {
        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessageMatches('/division by zero/i');
        (new Number('10'))->div(0);
    }

    public function testInvalidSplit(): void
    {
        $this->expectException(ArithmeticError::class);
        $this->expectExceptionMessageMatches('/cannot split/i');
        (new Number(200))->split(1);
    }

    public function testIssue16(): void
    {
        for ($scale = 0; $scale < 8; ++$scale) {
            $stub = new Number(15.8, $scale);

            for ($j = 2; $j < 50; ++$j) {
                [[$first], [$other, $i]] = $stub->split($j);
                $test                    = (new Number($other, $scale))->mul($i)->add($first);

                static::assertSame("{$test}", "{$stub}");
            }
        }
    }

    public function testSplit(): void
    {
        for ($scale = 0; $scale < 8; ++$scale) {
            $stub = new Number(100, $scale);

            for ($j = 2; $j < 50; ++$j) {
                [[$first], [$other, $i]] = $stub->split($j);
                $test                    = (new Number($other, $scale))->mul($i)->add($first);

                static::assertSame("{$test}", "{$stub}");
            }
        }
    }
}

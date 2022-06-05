<?php declare(strict_types=1);

namespace DL2\SDL\Tests;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use DL2\SDL\DateTime;
use DL2\SDL\DateTimeException;
use Generator;

/**
 * @internal
 * @covers \DL2\SDL\DateTime
 * @covers \DL2\SDL\DateTimeException
 */
final class DateTimeTest extends TestCase
{
    private const RANDOM_DAYS       = [1, 5, 8, 28];
    private const TESTING_DATETIME  = '2021-10-31';
    private const TESTING_TIMESTAMP = 1635705992;
    private static DateTime $stub;

    public static function setUpBeforeClass(): void
    {
        self::$stub = (new DateTime(self::TESTING_DATETIME))->setFormat(DateTime::DATE);
    }

    public function dataProviderAdd(): Generator
    {
        yield [new DateInterval('P1M')];

        yield ['P1M'];
    }

    public function dataProviderCtorArg0(): Generator
    {
        yield [new DateTime(self::TESTING_DATETIME)];

        yield [new \DateTime(self::TESTING_DATETIME)];

        yield [self::TESTING_DATETIME];

        yield [self::TESTING_TIMESTAMP];
    }

    public function dataProviderCtorArg1(): Generator
    {
        yield [new DateTimeZone('America/Sao_Paulo')];

        yield ['America/Sao_Paulo'];

        yield [null];
    }

    /**
     * @dataProvider dataProviderAdd
     */
    public function testAdd(DateInterval|string $input): void
    {
        $test = (new DateTime('2021-01-01'))->add($input);
        static::assertSame('2021-02-01', $test->format(DateTime::DATE));
    }

    public function testChangeMonths(): void
    {
        $tests = [
            '2021-11-30',
            '2021-12-31',
            '2022-01-31',
            '2022-02-28',
            '2022-03-31',
            '2022-04-30',
            '2022-05-31',
            '2022-06-30',
            '2022-07-31',
            '2022-08-31',
            '2022-09-30',
            '2022-10-31',
        ];

        foreach ($tests as $key => $test) {
            $next = self::$stub->addMonths($key + 1);
            static::assertSame("{$next}", $test);

            foreach (self::RANDOM_DAYS as $day) {
                $next = self::$stub->setDate(day: $day)->addMonths($key + 1);
                $test = (new DateTime($test))->setDate(day: $day);
                static::assertSame("{$test->setFormat(DateTime::DATE)}", "{$next}");
            }
        }

        // reset $next to the last item in $tests
        $next = self::$stub->addMonths(\count($tests));

        foreach (array_reverse($tests) as $key => $test) {
            // skip first index because $next is set to '2022-10-31'
            if (0 === $key) {
                continue;
            }

            $prev = $next->removeMonths($key);
            static::assertSame("{$prev}", $test);

            foreach (self::RANDOM_DAYS as $day) {
                $prev = $next->setDate(day: $day)->removeMonths($key);
                $test = (new DateTime($test))->setDate(day: $day);
                static::assertSame("{$test->setFormat(DateTime::DATE)}", "{$prev}");
            }
        }
    }

    public function testCreateFromFormat(): void
    {
        static::assertInstanceOf(
            Datetime::class,
            DateTime::createFromFormat('Y-m-d', self::TESTING_DATETIME)
        );

        $this->expectException(DateTimeException::class);
        DateTime::createFromFormat('invalid format', self::TESTING_DATETIME);
    }

    /**
     * @dataProvider dataProviderCtorArg0
     */
    public function testCtorArg0(int|string|DateTime|DateTimeInterface $datetime): void
    {
        $test = new DateTime($datetime);
        static::assertSame($test->format(DateTime::DATE), self::$stub->format(DateTime::DATE));

        $this->expectException(DateTimeException::class);
        new DateTime('invalid datetime string');
    }

    /**
     * @dataProvider dataProviderCtorArg1
     */
    public function testCtorArg1(null|DateTimeZone|string $timezone): void
    {
        $test = new DateTime(timezone: $timezone);
        static::assertSame($test->format('e'), self::$stub->format('e'));

        $this->expectException(DateTimeException::class);
        new DateTime(timezone: 'Invalid/Timezone');
    }

    /**
     * @dataProvider dataProviderCtorArg0
     */
    public function testDiff(null|int|string|DateTime|DateTimeInterface $target): void
    {
        $test = self::$stub->removeMonths(13)->diff($target);
        static::assertSame(1, $test->y);
    }

    public function testJsonSerializable(): void
    {
        static::assertJson(json_encode(self::$stub));
    }

    public function testMagicCalls(): void
    {
        static::assertInstanceOf(DateTime::class, self::$stub->setISODate(2021, 10));
        static::assertInstanceOf(DateTime::class, self::$stub->setTime(10, 20));
        static::assertInstanceOf(DateTime::class, self::$stub->setTimezone('UTC'));
        static::assertInstanceOf(DateTimeZone::class, self::$stub->getTimezone());
        static::assertIsInt(self::$stub->getOffset());
        static::assertIsInt(self::$stub->getTimestamp());
    }

    public function testStringable(): void
    {
        $test = static::$stub->setFormat(DateTime::DATE);

        static::assertSame("{$test}", self::TESTING_DATETIME);
    }

    /**
     * @dataProvider dataProviderAdd
     */
    public function testSub(DateInterval|string $input): void
    {
        $test = (new DateTime('2021-02-01'))->sub($input);
        static::assertSame('2021-01-01', $test->format(DateTime::DATE));
    }
}

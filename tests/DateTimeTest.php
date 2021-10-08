<?php declare(strict_types=1);

namespace DL2\SPL\Tests;

use DL2\SDL\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DateTimeTest extends TestCase
{
    private const MOCK_VALUE = '2021-01-31 10:00:00';

    public function testImmutability(): void
    {
        $now = $test = new DateTime(self::MOCK_VALUE);
        print_r(get_class_methods($now));

        exit();
        static::assertSame($now, $test);

        $now->add('P1M');
        $now->addMonths(1);
        $now->modify('+1month');
        $now->setDate(day: 10);
        $now->sub('P1D');

        $test->add('P1M');
        $test->addMonths(1);
        $test->modify('+1month');
        $test->setDate(day: 10);
        $test->sub('P1D');

        static::assertSame($now, $test);
    }
    // * @method DateTime     add(DateInterval $interval)
    // * @method int          getOffset()
    // * @method int          getTimestamp()
    // * @method DateTimeZone getTimezone()
    // * @method DateTime     modify(string $modifier)
    // * @method DateTime     setDate(int $year, int $month, int $day)
    // * @method DateTime     setISODate(int $year, int $week, ?int $dayOfWeek)
    // * @method DateTime     setTime(int $hour, int $minute, ?int $second, ?int $microsecond)
    // * @method DateTime     setTimezone(DateTimeZone $timezone)
    // * @method DateTime     sub(DateInterval $interval)
}

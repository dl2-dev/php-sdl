<?php declare(strict_types=1);

namespace DL2\SDL;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;
use Stringable;
use Throwable;

/**
 * @method int          getOffset()
 * @method int          getTimestamp()
 * @method DateTimeZone getTimezone()
 * @method DateTime     setISODate(int $year, int $week, ?int $dayOfWeek)
 * @method DateTime     setTime(int $hour, int $minute, ?int $second, ?int $microsecond)
 */
final class DateTime implements JsonSerializable, Stringable
{
    public const ATOM             = DateTimeInterface::ATOM;
    public const COOKIE           = DateTimeInterface::COOKIE;
    public const DATE             = 'Y-m-d';
    public const ISO8601          = DateTimeInterface::ISO8601;
    public const ISO8601_MYSQL    = 'Y-m-d H:i:s';
    public const RFC1036          = DateTimeInterface::RFC1036;
    public const RFC1123          = DateTimeInterface::RFC1123;
    public const RFC2822          = DateTimeInterface::RFC2822;
    public const RFC3339          = DateTimeInterface::RFC3339;
    public const RFC3339_EXTENDED = DateTimeInterface::RFC3339_EXTENDED;
    public const RFC7231          = DateTimeInterface::RFC7231;
    public const RFC822           = DateTimeInterface::RFC822;
    public const RFC850           = DateTimeInterface::RFC850;
    public const RSS              = DateTimeInterface::RSS;
    public const TIME             = 'H:i:s';
    public const TIMEZONE         = 'America/Sao_Paulo';
    public const W3C              = DateTimeInterface::W3C;

    private DateTimeImmutable $datetime;
    private string $format = self::ISO8601_MYSQL;

    public function __construct(
        self|DateTimeInterface|int|string $datetime = 'now',
        null|DateTimeZone|string $timezone = self::TIMEZONE
    ) {
        if (\is_int($datetime)) {
            $datetime = "@{$datetime}";
        }

        if ($datetime instanceof DateTimeInterface || $datetime instanceof self) {
            $datetime = $datetime->format(self::ISO8601);
        }

        try {
            $this->datetime = new DateTimeImmutable($datetime, self::mixedToTimeZone($timezone));
        } catch (Throwable $e) {
            throw new DateTimeException($e->getMessage(), $e->getCode());
        }
    }

    public function __call(string $method, array $params): int|DateTimeZone|self
    {
        /** @var mixed */
        $result = \call_user_func_array([$this->datetime, $method], $params);

        if ($result instanceof DateTimeInterface) {
            return $this->create($result);
        }

        /** @var DateTimeZone|int */
        return $result;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function add(DateInterval|string $spec): self
    {
        return $this->create($this->datetime->add(self::mixedToDateInterval($spec)));
    }

    /**
     * @psalm-param positive-int $spec
     */
    public function addMonths(int $spec = 1): self
    {
        return $this->changeMonths($spec);
    }

    public static function createFromFormat(
        string $format,
        string $datetime,
        DateTimeZone|string|null $timezone = null
    ): self {
        $timezone = self::mixedToTimeZone($timezone);

        /** @var DateTimeImmutable */
        $datetime = DateTimeImmutable::createFromFormat($format, $datetime, $timezone);
        $errors   = DateTimeImmutable::getLastErrors();

        // @codeCoverageIgnoreStart
        if (\count($errors['warnings'])) {
            throw new DateTimeException($errors['warnings'], 0);
        }
        // @codeCoverageIgnoreEnd

        if (\count($errors['errors'])) {
            throw new DateTimeException($errors['errors'], 0);
        }

        return new self($datetime, $timezone);
    }

    public function diff(self|DateTimeInterface|null|int|string $target = null): DateInterval
    {
        return $this->datetime->diff($this->create($target ?? Runtime::startedAt())->datetime);
    }

    public function format(?string $format = null): string
    {
        return $this->datetime->format($format ?? $this->format);
    }

    public function jsonSerialize(): string
    {
        return $this->format();
    }

    public function modify(string $modifier): self
    {
        return $this->create($this->datetime->modify($modifier));
    }

    /**
     * @psalm-param positive-int $spec
     */
    public function removeMonths(int $spec = 1): self
    {
        return $this->changeMonths($spec * -1);
    }

    /**
     * @template TParam as int|string|null
     *
     * @psalm-param TParam $year
     * @psalm-param TParam $month
     * @psalm-param TParam $day
     */
    public function setDate(
        int|string|null $year = null,
        int|string|null $month = null,
        int|string|null $day = null
    ): self {
        [$y, $m, $d] = explode('.', $this->format('Y.m.d'));

        $year  ??= $y;
        $month ??= $m;
        $day   ??= $d;

        return $this->create($this->datetime->setDate((int) $year, (int) $month, (int) $day));
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function setTimezone(null|DateTimeZone|string $timezone = self::TIMEZONE): self
    {
        return $this->create($this->datetime->setTimezone(self::mixedToTimeZone($timezone)));
    }

    public function sub(DateInterval|string $spec): self
    {
        return $this->create($this->datetime->sub(self::mixedToDateInterval($spec)));
    }

    private function changeMonths(int $spec): self
    {
        $spec = "{$spec}month";
        $next = $this->modify($spec);
        $day  = $next->format('d');

        if ($this->format('d') === $day) {
            return $next;
        }

        $next = $this->setDate(day: 1)->modify($spec);
        $day  = $next->format('t');

        if ($this->format('t') > $day) {
            $next = $next->setDate(day: $day);
        }

        return $next;
    }

    private function create(self|DateTimeInterface|int|string $datetime): self
    {
        return (new self($datetime, $this->getTimezone()))->setFormat($this->format);
    }

    private static function mixedToDateInterval(DateInterval|string $spec): DateInterval
    {
        if (\is_string($spec)) {
            $spec = new DateInterval(strtoupper($spec));
        }

        return $spec;
    }

    private static function mixedToTimeZone(DateTimeZone|string|null $timezone = self::TIMEZONE): DateTimeZone
    {
        if (!$timezone) {
            $timezone = self::TIMEZONE;
        }

        if (\is_string($timezone)) {
            try {
                $timezone = new DateTimeZone($timezone);
            } catch (Throwable $e) {
                throw new DateTimeException($e->getMessage(), $e->getCode());
            }
        }

        return $timezone;
    }
}

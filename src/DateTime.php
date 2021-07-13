<?php declare(strict_types=1);

namespace DL2\SDL;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;
use Throwable;

/**
 * @method DateTime     add(DateInterval $interval)
 * @method int          getOffset()
 * @method int          getTimestamp()
 * @method DateTimeZone getTimezone()
 * @method DateTime     modify(string $modifier)
 * @method DateTime     setDate(int $year, int $month, int $day)
 * @method DateTime     setISODate(int $year, int $week, ?int $dayOfWeek)
 * @method DateTime     setTime(int $hour, int $minute, ?int $second, ?int $microsecond)
 * @method DateTime     setTimezone(DateTimeZone $timezone)
 * @method DateTime     sub(DateInterval $interval)
 */
final class DateTime implements JsonSerializable
{
    public const ATOM             = DateTimeInterface::ATOM;
    public const COOKIE           = DateTimeInterface::COOKIE;
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
    public const TIMEZONE         = 'America/Sao_Paulo';
    public const W3C              = DateTimeInterface::W3C;

    private \DateTime $datetime;
    private string $format = self::ISO8601_MYSQL;

    /**
     * @param DateTimeInterface|int|string $datetime
     * @param ?DateTimeZone|string         $timezone
     */
    public function __construct($datetime = 'now', $timezone = self::TIMEZONE)
    {
        if (\is_int($datetime)) {
            $datetime = "@{$datetime}";
        }

        if (\is_object($datetime)) {
            $datetime = $datetime->format(self::ISO8601);
        }

        try {
            $this->datetime = new \DateTime($datetime, self::mixedToTimeZone($timezone));
        } catch (Throwable $err) {
            throw new DateTimeException($err->getMessage());
        }
    }

    public function __call(string $method, array $params): mixed
    {
        /** @var mixed */
        $result = \call_user_func_array([$this->datetime, $method], $params);

        if ('get' === substr($method, 0, 3)) {
            return $result;
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->format($this->format);
    }

    /**
     * @param ?DateTimeZone|string $timezone
     */
    public static function createFromFormat(string $format, string $datetime, $timezone = null): self
    {
        $timezone = self::mixedToTimeZone($timezone);
        $datetime = \DateTime::createFromFormat($format, $datetime, $timezone);
        $errors   = \DateTime::getLastErrors();

        if (\count($errors['warnings'])) {
            throw new DateTimeException($errors['warnings']);
        }

        if (\count($errors['errors'])) {
            throw new DateTimeException($errors['errors']);
        }

        return new self($datetime, $timezone);
    }

    /**
     * @param ?DateTimeInterface|int|string $target
     */
    public function diff($target = null): DateInterval
    {
        return $this->datetime->diff((new self($target ?? Runtime::startedAt()))->datetime);
    }

    public function format(?string $format = null): string
    {
        return $this->datetime->format($format ?? $this->format);
    }

    public function jsonSerialize(): string
    {
        return $this->format();
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param ?DateTimeZone|string $timezone
     */
    private static function mixedToTimeZone($timezone = self::TIMEZONE): DateTimeZone
    {
        if (!$timezone) {
            $timezone = self::TIMEZONE;
        }

        if (\is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        return $timezone;
    }
}
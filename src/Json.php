<?php declare(strict_types=1);

namespace DL2\SDL;

use ArrayAccess;
use ArrayObject;
use IteratorAggregate;
use OutOfBoundsException;
use Stringable;
use Traversable;
use ValueError;

/**
 * @template-implements IteratorAggregate<mixed>
 */
final class Json implements IteratorAggregate, Stringable
{
    private const DECODE_FLAGS  = 1;
    private const ENCODE_FLAGS  = 0;
    private const FLAGS_COMMON  = JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;
    private const FLAGS_DEFAULT = [
        self::DECODE_FLAGS => self::FLAGS_COMMON | JSON_BIGINT_AS_STRING,
        self::ENCODE_FLAGS => self::FLAGS_COMMON | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION,
    ];

    /**
     * Create a cool Json object.
     *
     * @note(douggr): if the given $json is a string, it MUST be a valid json string. e.g.:
     *      'foo': JsonException is thrown (expects '"foo"')
     *      '{ }': ok
     *      '[ ]': ok
     */
    public function __construct(private mixed $json = '{}')
    {
        switch (\gettype($json)) {
            // @codeCoverageIgnoreStart
            case 'boolean':
            case 'integer':
            case 'double':
            case 'NULL':
                $json = $this->encode($json);

            // no break
            case 'string':
                /** @var mixed */
                $json = $this->decode("{$json}");

            // @codeCoverageIgnoreEnd
            // no break
            default:
                /** @psalm-suppress PossiblyInvalidArgument */
                if (\is_array($json) || \is_object($json)) {
                    $this->json = new ArrayObject($json, ArrayObject::ARRAY_AS_PROPS);
                } else {
                    $this->json = $json;
                }
        }
    }

    public function __get(string $name): mixed
    {
        $this->throwValueErrorOnInvalidJson($name, 'read');

        /** @psalm-suppress MixedMethodCall */
        return Runtime::wrapError(fn (): mixed => $this->json->offsetGet($name), OutOfBoundsException::class);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->throwValueErrorOnInvalidJson($name, 'assign');

        /** @psalm-suppress MixedMethodCall */
        $this->json->offsetSet($name, $value);
    }

    public function __toString(): string
    {
        return $this->encode($this->json);
    }

    public function decode(string|Stringable $json, int $flags = 0): mixed
    {
        return json_decode("{$json}", flags: $this->getFlags($flags, self::DECODE_FLAGS));
    }

    public function encode(mixed $value = null, int $flags = 0): string
    {
        return json_encode($value, $this->getFlags($flags));
    }

    public function getIterator(): Traversable
    {
        if ($this->json instanceof ArrayObject) {
            yield from $this->json->getIterator();
        } else {
            yield [$this->json];
        }
    }

    /**
     * Reads a json file into a Json object.
     *
     * @param non-empty-string $filename
     */
    public static function read(string $filename): self
    {
        return new self(FileObject::read($filename));
    }

    /**
     * Write data to a json file.
     *
     * @param non-empty-string $filename
     */
    public function write(string $filename, bool $pretty = true): int
    {
        return FileObject::write(
            $filename,
            "{$this->encode($this->json, $pretty ? JSON_PRETTY_PRINT : 0)}\n"
        );
    }

    /**
     * @param self::DECODE_FLAGS|self::ENCODE_FLAGS $type
     */
    private function getFlags(int $flags = 0, int $type = self::ENCODE_FLAGS): int
    {
        return self::FLAGS_DEFAULT[$type] | $flags;
    }

    /**
     * @template T as 'read'|'assign'
     *
     * @param T $type
     */
    private function throwValueErrorOnInvalidJson(string $name, string $type): void
    {
        if (!($this->json instanceof ArrayAccess)) {
            throw new ValueError(
                sprintf('Attempt to %s property "%s" on %s', $type, $name, \gettype($this->json))
            );
        }
    }
}

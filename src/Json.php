<?php declare(strict_types=1);

namespace DL2\SDL;

use IteratorAggregate;
use JsonException;
use SplFixedArray;
use Traversable;

final class Json implements IteratorAggregate
{
    private const FLAGS_DECODE = 1;
    private const FLAGS_ENCODE = 0;

    private ?string $filename;
    private SplFixedArray $flags;
    private mixed $json;

    public function __construct(mixed $json = null)
    {
        $this->flags = new SplFixedArray(2);

        // prettier-ignore
        $this
            ->setDecodeFlags(JSON_BIGINT_AS_STRING)
            ->setEncodeFlags(JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION)
        ;

        if ($json) {
            $this->decode($this->encode($json));
        }
    }

    public function __get(string $who): mixed
    {
        /** @psalm-suppress MixedPropertyFetch */
        return self::wrapError(fn (): mixed => $this->json->{$who});
    }

    public function __set(string $who, mixed $data): void
    {
        self::wrapError(fn (): mixed => ($this->json->{$who} = $data));
    }

    public function decode(string $json, int $flags = 0): self
    {
        $this->json = json_decode($json, null, 512, $this->getDecodeFlags($flags));

        return $this;
    }

    public function encode(mixed $data = null, int $flags = 0): string
    {
        return json_encode($data ?? $this->json, $this->getEncodeFlags($flags));
    }

    public function getDecodeFlags(int $flags = 0): int
    {
        return $this->getFlags(self::FLAGS_DECODE) | $flags;
    }

    public function getEncodeFlags(int $flags = 0): int
    {
        return $this->getFlags() | $flags;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function getIterator(): Traversable
    {
        yield from (array) $this->json;
    }

    public static function read(string $filename, int $flags = 0): self
    {
        // prettier-ignore
        return (new self())
            ->setDecodeFlags($flags)
            ->setFilename($filename)
            ->decode(FileObject::read($filename))
        ;
    }

    public function save(?string $filename = null, mixed $data = null, int $flags = 0): self
    {
        /** @psalm-suppress PossiblyNullArgument */
        FileObject::write($filename ?? $this->filename, $this->encode($data, $flags | JSON_PRETTY_PRINT));

        return $this;
    }

    public function setDecodeFlags(int $flags = 0): self
    {
        return $this->setFlags($flags, self::FLAGS_DECODE);
    }

    public function setEncodeFlags(int $flags = 0): self
    {
        return $this->setFlags($flags);
    }

    public function setFilename(string $filename): self
    {
        $this->filename = FileObject::resolveFilename($filename);

        return $this;
    }

    private function getFlags(int $type = self::FLAGS_ENCODE): int
    {
        /** @var int */
        return $this->flags[$type];
    }

    private function setFlags(int $flags = 0, int $type = self::FLAGS_ENCODE): self
    {
        $this->flags[$type] = $flags | JSON_THROW_ON_ERROR;

        return $this;
    }

    private static function wrapError(callable $fn): mixed
    {
        return Runtime::wrapError($fn, JsonException::class);
    }
}

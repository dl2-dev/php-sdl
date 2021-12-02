<?php declare(strict_types=1);

namespace DL2\SDL;

use LogicException;
use RuntimeException;
use SplFileObject;

final class FileObject extends SplFileObject
{
    /**
     * @param non-empty-string $filename
     */
    public function __construct(string $filename, string $mode = 'r+')
    {
        try {
            parent::__construct(self::resolveFilename($filename), $mode, false);
        } catch (RuntimeException $e) {
            throw new IOException($e->getMessage());
        }

        $this->setFlags(self::DROP_NEW_LINE | self::SKIP_EMPTY | self::READ_CSV);
    }

    public static function mkdir(string $pathname, int $mode = 0744): bool
    {
        if (!is_dir($pathname)) {
            // @codeCoverageIgnoreStart
            /** @var bool */
            return self::wrapError(fn (): bool => mkdir($pathname, $mode, true));
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * @param non-empty-string $filename
     */
    public static function read(string $filename): string
    {
        /** @var string */
        return self::wrapError(fn (): string => file_get_contents(self::resolveFilename($filename, true)));
    }

    /**
     * @param non-empty-string $filename
     */
    public static function resolveFilename(string $filename, bool $throwOnError = false): string
    {
        if (!$filename) {
            throw new LogicException('Argument #1 ($filename) must be a non-empty string');
        }

        if ('/' !== $filename[0]) {
            $filename = sprintf('%s/%s', Runtime::cwd(), $filename);
        }

        if ($throwOnError && !file_exists($filename)) {
            throw new IOException("{$filename}: Failed to open stream: No such file or directory");
        }

        return $filename;
    }

    /**
     * @param non-empty-string             $filename
     * @param list<string>|resource|string $data
     */
    public static function write(string $filename, mixed $data, int $flags = LOCK_EX): int
    {
        $filename = self::resolveFilename($filename);
        self::mkdir(\dirname($filename));

        /** @var int */
        return self::wrapError(fn (): int => file_put_contents($filename, $data, $flags));
    }

    /**
     * @param callable():mixed $fn
     */
    private static function wrapError(callable $fn): mixed
    {
        return Runtime::wrapError($fn, IOException::class);
    }
}

<?php declare(strict_types=1);

namespace DL2\SDL;

use ErrorException;

final class Runtime
{
    public static function cwd(): string
    {
        return (string) DL2_SDL_CWD;
    }

    public static function getElapsedTime(): string
    {
        return (new DateTime())->diff()->format('%ad %Hh%Im%Ss');
    }

    public static function rusage(bool $current = true): array
    {
        if ($current) {
            return getrusage();
        }

        /** @var array<string,int> */
        return DL2_SDL_RUSAGE_INIT;
    }

    public static function startedAt(): int
    {
        return (int) DL2_SDL_STARTED_AT;
    }

    /**
     * @param class-string<\Throwable> $class
     *
     * @psalm-suppress InvalidReturnType
     */
    public static function wrapError(callable $fn, ?string $class): mixed
    {
        set_error_handler(function (int $code, string $message, string $filename, int $line): bool {
            throw new ErrorException($message, $code, E_ERROR, $filename, $line);
        }, E_ALL);

        try {
            return $fn();
        } catch (ErrorException $err) {
            if ($class) {
                throw new $class($err->getMessage(), $err->getCode());
            }

            throw $err;
        } finally {
            restore_error_handler();
        }
    }
}

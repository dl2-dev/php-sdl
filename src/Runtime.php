<?php declare(strict_types=1);

namespace DL2\SDL;

use DateTimeInterface;
use ErrorException;
use Throwable;

final class Runtime
{
    public static function cwd(): string
    {
        return (string) DL2_SDL_CWD;
    }

    public static function getElapsedTime(DateTime|DateTimeInterface|int|string $since = 'now'): string
    {
        return (new DateTime($since))->diff()->format('%ad %Hh%Im%Ss');
    }

    public static function rusage(bool $current = true): array
    {
        if ($current) {
            return getrusage();
        }

        /** @var array<string,numeric-string> */
        return DL2_SDL_RUSAGE_INIT;
    }

    public static function startedAt(): int
    {
        return (int) DL2_SDL_STARTED_AT;
    }

    /**
     * @param callable():mixed         $fn
     * @param class-string<Throwable>  $class
     * @param callable(Throwable):void $onError
     */
    public static function wrapError(callable $fn, ?string $class = null, ?callable $onError = null): mixed
    {
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
            throw new ErrorException($errstr, $errno, E_ERROR, $errfile, $errline); // NOSONAR
        }, E_ALL);

        try {
            return $fn();
        } catch (ErrorException $e) {
            if ($onError) {
                $onError($e);
            }

            if ($class) {
                $e = new $class($e->getMessage(), $e->getCode());
            }

            throw $e;
        } finally {
            restore_error_handler();
        }
    }
}

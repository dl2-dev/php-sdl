<?php declare(strict_types=1);

namespace DL2\SDL;

trait Singleton
{
    private static ?object $instance = null;

    private function __construct()
    {
        // not implemented
    }

    public static function getInstance(mixed ...$args): object
    {
        if (null === self::$instance) {
            self::$instance = self::createInstance(...$args);
        }

        return self::$instance;
    }

    private static function createInstance(mixed ...$args): object
    {
        return new self(...$args);
    }
}

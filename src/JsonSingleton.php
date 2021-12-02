<?php declare(strict_types=1);

namespace DL2\SDL;

use LogicException;

trait JsonSingleton
{
    use Singleton;

    private static function createInstance(): Json
    {
        if (!\defined('self::FILENAME')) {
            throw new LogicException('string const FILENAME is not defined in ' . self::class);
        }

        return Json::read(self::FILENAME);
    }
}

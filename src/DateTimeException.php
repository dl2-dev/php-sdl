<?php declare(strict_types=1);

namespace DL2\SDL;

use RuntimeException;

class DateTimeException extends RuntimeException
{
    public function __construct(array|string $e, int|string $code)
    {
        $e = (array) $e;

        parent::__construct((string) array_shift($e), (int) $code);
    }
}

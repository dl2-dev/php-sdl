<?php declare(strict_types=1);

namespace DL2\SDL;

use RuntimeException;

class DateTimeException extends RuntimeException
{
    public function __construct(array|string $error)
    {
        $error = (array) $error;

        parent::__construct((string) array_shift($error));
    }
}

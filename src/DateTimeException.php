<?php declare(strict_types=1);

namespace DL2\SDL;

use RuntimeException;

class DateTimeException extends RuntimeException
{
    /**
     * @param array|string $error
     */
    public function __construct($error)
    {
        $error = (array) $error;

        parent::__construct((string) array_shift($error));
    }
}

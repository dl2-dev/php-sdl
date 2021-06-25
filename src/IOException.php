<?php declare(strict_types=1);

namespace DL2\SDL;

use RuntimeException;
use Throwable;

class IOException extends RuntimeException
{
    public function __construct(string $message, int $code = 2, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

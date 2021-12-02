<?php declare(strict_types=1);

namespace DL2\SDL\Tests\Support;

use DL2\SDL;

class Singleton
{
    use SDL\Singleton;

    private function __construct(public string $arg0 = 'foo')
    {
        // not implemented
    }
}

<?php declare(strict_types=1);

namespace Asterios\Test\Stubs;

class LegacyMiddleware
{
    public static bool $called = false;

    public function handle(): bool
    {
        self::$called = true;

        return true;
    }
}

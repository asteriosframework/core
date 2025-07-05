<?php declare(strict_types=1);

namespace Asterios\Test\Stubs;

class VersionMiddleware
{
    public function handle(): bool
    {
        return true;
    }
}
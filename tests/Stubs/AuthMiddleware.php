<?php declare(strict_types=1);

namespace Asterios\Test\Stubs;

class AuthMiddleware
{
    public function handle(): bool
    {
        return true;
    }
}
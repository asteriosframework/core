<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Http;

interface MiddlewareInterface
{
    public function handle(): bool;
}
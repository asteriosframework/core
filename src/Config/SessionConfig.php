<?php declare(strict_types=1);

namespace Asterios\Core\Config;

final readonly class SessionConfig
{
    public function __construct(
        public string $namespace = 'user',
        public bool $autoStart = true,
        public bool $strictMode = true,
        public bool $httpOnly = true,
        public bool $secureCookies = false,
        public string $sameSite = 'Lax',
        public int $cookieLifetime = 0,
        public string $cookiePath = '/',
        public string $cookieDomain = '',
    ) {
    }
}

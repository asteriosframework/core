<?php
declare(strict_types=1);

namespace Asterios\Core;

use OTPHP\TOTPInterface;

final readonly class GoogleAuthenticator
{
    private TOTPInterface $totp;

    public function __construct(TOTPInterface $totp)
    {
        $this->totp = $totp;
    }

    public function getSecret(): string
    {
        return $this->totp->getSecret();
    }

    public function getProvisioningUri(): string
    {
        return $this->totp->getProvisioningUri();
    }


    public function verify(string $code, int $leeway = 1, ?int $timestamp = null): bool
    {
        return $this->totp->verify($code, $timestamp, $leeway);
    }
}

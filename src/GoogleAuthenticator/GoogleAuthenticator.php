<?php
declare(strict_types=1);

namespace Asterios\Core\GoogleAuthenticator;

use Asterios\Core\Contracts\GoogleAuthenticator\GoogleAuthenticatorInterface;
use OTPHP\TOTPInterface;

final readonly class GoogleAuthenticator implements GoogleAuthenticatorInterface
{
    private TOTPInterface $totp;

    public function __construct(TOTPInterface $totp)
    {
        $this->totp = $totp;
    }

    /**
     * @inheritDoc
     */
    public function getSecret(): string
    {
        return $this->totp->getSecret();
    }

    /**
     * @inheritDoc
     */
    public function getProvisioningUri(): string
    {
        return $this->totp->getProvisioningUri();
    }

    /**
     * @inheritDoc
     */
    public function verify(string $code, int $leeway = 1, ?int $timestamp = null): bool
    {
        return $this->totp->verify($code, $timestamp, $leeway);
    }
}

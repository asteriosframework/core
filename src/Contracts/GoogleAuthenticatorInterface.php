<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface GoogleAuthenticatorInterface
{
    /**
     * @return string
     */
    public function getSecret(): string;

    /**
     * @return string
     */
    public function getProvisioningUri(): string;

    /**
     * @param string $code
     * @param int $leeway
     * @param int|null $timestamp
     * @return bool
     */
    public function verify(string $code, int $leeway = 1, int $timestamp = null): bool;
}
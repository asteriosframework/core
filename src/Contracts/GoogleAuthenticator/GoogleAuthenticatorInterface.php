<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\GoogleAuthenticator;

use Asterios\Core\Exception\GoogleAuthenticatorRandomBytesException;

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

    /**
     * @param int $length
     * @return string
     * @throws GoogleAuthenticatorRandomBytesException
     */
    public function generateBase32Secret(int $length = 16): string;
}

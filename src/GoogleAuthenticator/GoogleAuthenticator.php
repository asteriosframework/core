<?php
declare(strict_types=1);

namespace Asterios\Core\GoogleAuthenticator;

use Asterios\Core\Contracts\GoogleAuthenticator\GoogleAuthenticatorInterface;
use Asterios\Core\Exception\GoogleAuthenticatorRandomBytesException;
use OTPHP\TOTPInterface;
use Random\RandomException;

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

    /**
     * @inheritDoc
     */
    public function generateBase32Secret(int $length = 16): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        try
        {
            $randomBytes = random_bytes($length);
        }
        // @codeCoverageIgnoreStart
        catch (RandomException $e)
        {
            throw new GoogleAuthenticatorRandomBytesException($e->getMessage());
        }
        /// @codeCoverageIgnoreEnd
        $binaryString = '';

        foreach (str_split($randomBytes) as $char)
        {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($binaryString, 5) as $chunk)
        {
            $chunk = str_pad($chunk, 5, '0');
            $encoded .= $alphabet[bindec($chunk)];
        }

        return $encoded;
    }
}

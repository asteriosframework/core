<?php declare(strict_types=1);

namespace Asterios\Core\Network\Ip;

use Asterios\Core\Contracts\Network\Ip\Ipv6MatcherInterface;
use Asterios\Core\Exception\Network\Ip\InvalidIpException;
use Asterios\Core\Exception\Network\Ip\InvalidIpRangeException;

final readonly class Ipv6Matcher implements Ipv6MatcherInterface
{
    /**
     * @inheritDoc
     */
    public function inRange(string $ip, string $range): bool
    {
        $ipBinary = @inet_pton($ip);

        if ($ipBinary === false || strlen($ipBinary) !== 16)
        {
            throw new InvalidIpException(sprintf('Invalid IPv6 address: %s', $ip));
        }

        if (!str_contains($range, '/'))
        {
            throw new InvalidIpRangeException('IPv6 ranges must use CIDR notation.');
        }

        [$subnet, $prefix] = explode('/', $range, 2);

        if (!is_numeric($prefix))
        {
            throw new InvalidIpRangeException('Invalid IPv6 CIDR prefix.');
        }

        $subnetBinary = @inet_pton($subnet);

        if ($subnetBinary === false || strlen($subnetBinary) !== 16)
        {
            throw new InvalidIpRangeException('Invalid IPv6 subnet.');
        }

        $prefix = (int) $prefix;

        if ($prefix < 0 || $prefix > 128)
        {
            throw new InvalidIpRangeException('IPv6 prefix must be between 0 and 128.');
        }

        return $this->matchesPrefix($ipBinary, $subnetBinary, $prefix);
    }

    /**
     * @inheritDoc
     */
    public function normalize(string $ip): string
    {
        $binary = @inet_pton($ip);

        if ($binary === false || strlen($binary) !== 16)
        {
            throw new InvalidIpException(sprintf('Invalid IPv6 address: %s', $ip));
        }

        return bin2hex($binary);
    }

    /**
     * @param string $ip
     * @param string $subnet
     * @param int $prefix
     * @return bool
     */
    private function matchesPrefix(string $ip, string $subnet, int $prefix): bool
    {
        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;

        $prefixBytes = substr($subnet, 0, $fullBytes);

        if ($fullBytes > 0 && !str_starts_with($ip, $prefixBytes))
        {
            return false;
        }

        if ($remainingBits === 0)
        {
            return true;
        }

        $mask = ~(255 >> $remainingBits) & 255;

        return (
            (ord($ip[$fullBytes]) & $mask)
            ===
            (ord($subnet[$fullBytes]) & $mask)
        );
    }
}
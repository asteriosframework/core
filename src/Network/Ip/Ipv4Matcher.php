<?php declare(strict_types=1);

namespace Asterios\Core\Network\Ip;

use Asterios\Core\Contracts\Network\Ip\Ipv4MatcherInterface;
use Asterios\Core\Exception\Network\Ip\InvalidIpException;
use Asterios\Core\Exception\Network\Ip\InvalidIpRangeException;

final readonly class Ipv4Matcher implements Ipv4MatcherInterface
{
    /**
     * @inheritDoc
     */
    public function inRange(string $ip, string $range): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            throw new InvalidIpException(sprintf('Invalid IPv4 address: %s', $ip));
        }

        if (str_contains($range, '*'))
        {
            return $this->matchesWildcard($ip, $range);
        }

        if (str_contains($range, '/'))
        {
            return $this->matchesCidr($ip, $range);
        }

        if (str_contains($range, '-'))
        {
            return $this->matchesRange($ip, $range);
        }

        throw new InvalidIpRangeException(sprintf('Invalid IPv4 range: %s', $range));
    }

    /**
     * @param string $ip
     * @param string $range
     * @return bool
     * @throws InvalidIpRangeException
     */
    private function matchesWildcard(string $ip, string $range): bool
    {
        $lower = str_replace('*', '0', $range);
        $upper = str_replace('*', '255', $range);

        return $this->matchesRange($ip, $lower . '-' . $upper);
    }

    /**
     * @param string $ip
     * @param string $range
     * @return bool
     * @throws InvalidIpRangeException
     */
    private function matchesCidr(string $ip, string $range): bool
    {
        [$subnet, $mask] = explode('/', $range, 2);

        if (!is_numeric($mask))
        {
            throw new InvalidIpRangeException('Only CIDR masks are supported for IPv4.');
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false)
        {
            throw new InvalidIpRangeException('Invalid CIDR subnet.');
        }

        $maskLong = -1 << (32 - (int) $mask);

        return (($ipLong & $maskLong) === ($subnetLong & $maskLong));
    }

    /**
     * @param string $ip
     * @param string $range
     * @return bool
     * @throws InvalidIpRangeException
     */
    private function matchesRange(string $ip, string $range): bool
    {
        [$start, $end] = explode('-', $range, 2);

        $ipLong = ip2long($ip);
        $startLong = ip2long($start);
        $endLong = ip2long($end);

        if ($ipLong === false || $startLong === false || $endLong === false)
        {
            throw new InvalidIpRangeException('Invalid IPv4 range boundaries.');
        }

        return $ipLong >= $startLong && $ipLong <= $endLong;
    }
}
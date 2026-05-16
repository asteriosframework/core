<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Network\Ip;

use Asterios\Core\Exception\Network\Ip\InvalidIpException;
use Asterios\Core\Exception\Network\Ip\InvalidIpRangeException;

interface Ipv6MatcherInterface
{
    /**
     * @param string $ip
     * @param string $range
     * @return bool
     * @throws InvalidIpRangeException
     * @throws InvalidIpException
     */
    public function inRange(string $ip, string $range): bool;

    /**
     * @param string $ip
     * @return string
     * @throws InvalidIpException
     */
    public function normalize(string $ip): string;
}
<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Network\Ip;

use Asterios\Core\Enum\Network\Ip\IpVersion;
use Asterios\Core\Exception\Network\Ip\InvalidIpException;
use Asterios\Core\Exception\Network\Ip\InvalidIpRangeException;

interface IpInterface
{
    /**
     * @param string $ip
     * @return bool
     */
    public function isValid(string $ip): bool;

    /**
     * @param string $ip
     * @return IpVersion
     * @throws InvalidIpException
     */
    public function version(string $ip): IpVersion;

    /**
     * @param string $ip
     * @return string
     * @throws InvalidIpException
 */
    public function normalize(string $ip): string;

    /**
     * @param string $ip
     * @param string $range
     * @return bool
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function inRange(string $ip, string $range): bool;
}

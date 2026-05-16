<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Network\Ip;

use Asterios\Core\Enum\Network\Ip\IpVersion;
use Asterios\Core\Exception\Network\Ip\InvalidIpException;

interface IpValidatorInterface
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
}
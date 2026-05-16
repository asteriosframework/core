<?php declare(strict_types=1);

namespace Asterios\Core\Network\Ip;

use Asterios\Core\Contracts\Network\Ip\IpValidatorInterface;
use Asterios\Core\Enum\Network\Ip\IpVersion;
use Asterios\Core\Exception\Network\Ip\InvalidIpException;

final readonly class IpValidator implements IpValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function isValid(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * @inheritDoc
     */
    public function version(string $ip): IpVersion
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            return IpVersion::IPv4;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            return IpVersion::IPv6;
        }

        throw new InvalidIpException(sprintf('Invalid IP address: %s', $ip));
    }
}
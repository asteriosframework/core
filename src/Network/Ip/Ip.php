<?php declare(strict_types=1);

namespace Asterios\Core\Network\Ip;

use Asterios\Core\Contracts\Network\Ip\IpInterface;
use Asterios\Core\Contracts\Network\Ip\Ipv4MatcherInterface;
use Asterios\Core\Contracts\Network\Ip\Ipv6MatcherInterface;
use Asterios\Core\Contracts\Network\Ip\IpValidatorInterface;
use Asterios\Core\Enum\Network\Ip\IpVersion;

final readonly class Ip implements IpInterface
{
    public function __construct(
        private IpValidatorInterface $validator = new IpValidator(),
        private Ipv4MatcherInterface $ipv4Matcher = new Ipv4Matcher(),
        private Ipv6MatcherInterface $ipv6Matcher = new Ipv6Matcher(),
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isValid(string $ip): bool
    {
        return $this->validator->isValid($ip);
    }

    /**
     * @inheritDoc
     */
    public function version(string $ip): IpVersion
    {
        return $this->validator->version($ip);
    }

    /**
     * @inheritDoc
     */
    public function normalize(string $ip): string
    {
        return match ($this->version($ip))
        {
            IpVersion::IPv4 => $ip,
            IpVersion::IPv6 => $this->ipv6Matcher->normalize($ip),
        };
    }

    /**
     * @inheritDoc
     */
    public function inRange(string $ip, string $range): bool
    {
        return match ($this->version($ip))
        {
            IpVersion::IPv4 => $this->ipv4Matcher->inRange($ip, $range),
            IpVersion::IPv6 => $this->ipv6Matcher->inRange($ip, $range),
        };
    }
}

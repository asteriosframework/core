<?php declare(strict_types=1);

namespace Asterios\Core\Enum\Network\Ip;

enum IpVersion: int
{
    case IPv4 = 4;
    case IPv6 = 6;
}

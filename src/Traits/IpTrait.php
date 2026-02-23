<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Ip;

trait IpTrait
{
    /** @var Ip|null */
    protected Ip|null $ip;

    public function setIp(Ip $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getIp(): Ip
    {
        return $this->ip ?? new Ip();
    }

}

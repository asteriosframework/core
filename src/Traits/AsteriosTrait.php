<?php declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Asterios;

trait AsteriosTrait
{
    /** @var Asterios|null */
    protected $asterios;

    public function setAsterios(Asterios $asterios): self
    {
        $this->asterios = $asterios;

        return $this;
    }

    public function getAsterios(): Asterios
    {
        return $this->asterios ?? new Asterios();
    }
}
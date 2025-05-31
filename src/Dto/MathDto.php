<?php declare(strict_types=1);

namespace Asterios\Core\Dto;

class MathDto
{
    public float $tax = 19;
    public string $currency = 'EUR';

    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}

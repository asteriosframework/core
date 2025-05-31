<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface TimestampColumnBuilderInterface
{
    /**
     * @param int $value
     * @return self
     */
    public function precision(int $value): self;

    /**
     * @return self
     */
    public function nullable(): self;
}

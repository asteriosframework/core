<?php declare(strict_types=1);

namespace Asterios\Core\Config;

final readonly class MathConfig
{
    public function __construct(
        public float $defaultTax = 19.0,
        public int $defaultPrecision = 2,
    ) {
    }
}

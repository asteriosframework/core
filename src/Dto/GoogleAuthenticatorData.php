<?php declare(strict_types=1);

namespace Asterios\Core\Dto;

use Asterios\Core\Data;

class GoogleAuthenticatorData extends Data
{
    public function __construct(
        public string $secret,
        public string $label,
        public string $issuer,
        public int $digits = 6,
        public int $period = 30,
        public string $digest = 'sha1'
    ) {
    }
}
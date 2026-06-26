<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class GeoData extends Data
{
    public function __construct(
        public float $latitude,
        public float $longitude
    ) {
    }
}
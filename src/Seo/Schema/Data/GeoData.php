<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Data;

use Asterios\Core\Data;

final class GeoData extends Data
{
    public float $latitude;

    public float $longitude;
}
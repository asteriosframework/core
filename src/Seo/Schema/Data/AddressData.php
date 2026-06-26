<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Data;

use Asterios\Core\Data;

final class AddressData extends Data
{
    public string $street;

    public string $postalCode;

    public string $city;

    public string $region;

    public string $country;
}
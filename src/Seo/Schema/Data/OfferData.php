<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class OfferData extends Data
{
    public string $name;

    public float $price;

    public string $currency = 'EUR';

    public string $availability = 'https://schema.org/InStock';

    public ?string $url = null;

    public ?string $itemId = null;
}
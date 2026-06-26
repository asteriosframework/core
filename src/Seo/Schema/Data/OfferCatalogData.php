<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Data;

use Asterios\Core\Data;

final class OfferCatalogData extends Data
{
    public string $name;

    public string $url;

    /**
     * @var OfferData[]
     */
    public array $offers = [];
}
<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Node;

use Asterios\Core\Seo\Schema\Enums\Contracts\Node;
use Asterios\Core\Seo\Schema\Enums\Data\OfferCatalogData;
use Asterios\Core\Seo\Schema\Enums\SchemaIdEnum;

final readonly class OfferCatalog implements Node
{
    public function __construct(
        private OfferCatalogData $data,
    ) {
    }

    public function build(): array
    {
        return [
            '@type' => 'OfferCatalog',
            '@id' => $this->data->url . SchemaIdEnum::OFFER_CATALOG,
            'name' => $this->data->name,
            'url' => $this->data->url,
            'itemListElement' => $this->offers(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function offers(): array
    {
        return array_map(
            static fn($offer) => (new Offer($offer))->build(),
            $this->data->offers,
        );
    }
}
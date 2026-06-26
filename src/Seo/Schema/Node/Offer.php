<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Node;

use Asterios\Core\Seo\Schema\Enums\Contracts\Node;
use Asterios\Core\Seo\Schema\Enums\Data\OfferData;

final readonly class Offer implements Node
{
    public function __construct(
        private OfferData $data,
    ) {
    }

    public function build(): array
    {
        $offer = [
            '@type' => 'Offer',
            'name' => $this->data->name,
            'price' => number_format($this->data->price, 2, '.', ''),
            'priceCurrency' => $this->data->currency,
            'availability' => $this->data->availability,
        ];

        if ($this->data->url !== null) {
            $offer['url'] = $this->data->url;
        }

        if ($this->data->itemId !== null) {
            $offer['itemOffered'] = [
                '@id' => $this->data->itemId,
            ];
        }

        return $offer;
    }
}
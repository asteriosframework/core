<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Node;

use Asterios\Core\Seo\Schema\Contracts\Node;
use Asterios\Core\Seo\Schema\Data\ResourceData;
use Asterios\Core\Seo\Schema\Enums\SchemaIdEnum;

final readonly class Legalpage implements Node
{
    public function __construct(
        private ResourceData $data,
    ) {
    }

    public function build(): array
    {
        return [
            '@type' => 'WebPage',
            '@id' => $this->data->url . SchemaIdEnum::PAGE,
            'name' => $this->data->name,
            'url' => $this->data->url,
        ];
    }
}
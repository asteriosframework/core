<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Node;

use Asterios\Core\Seo\Schema\Enums\Contracts\Node;
use Asterios\Core\Seo\Schema\Enums\Data\ResourceData;

final readonly class Accommodation implements Node
{
    public function __construct(
        private ResourceData $data,
    ) {
    }

    public function build(): array
    {
        return [
            '@type' => 'Accommodation',
            'name' => $this->data->name,
            'url' => $this->data->url,
        ];
    }
}
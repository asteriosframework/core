<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Node;

use Asterios\Core\Seo\Schema\Contracts\Node;
use Asterios\Core\Seo\Schema\Data\ResourceData;

final readonly class Website implements Node
{
    public function __construct(
        private ResourceData $data,
    ) {
    }

    public function build(): array
    {
        return [
            '@type' => 'WebSite',
            'name' => $this->data->name,
            'url' => $this->data->url,
        ];
    }
}
<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Node;

use Asterios\Core\Seo\Schema\Enums\Contracts\Node;
use Asterios\Core\Seo\Schema\Enums\Data\BreadcrumbData;

readonly class Breadcrumb implements Node
{
    public function __construct(
        private BreadcrumbData $data,
    ) {
    }

    public function build(): array
    {
        $items = [];

        foreach ($this->data->items as $position => $item) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item->name,
                'item' => $item->url,
            ];
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
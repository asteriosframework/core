<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class BreadcrumbData extends Data
{
    /**
     * @var BreadcrumbItemData[]
     */
    public array $items = [];
}
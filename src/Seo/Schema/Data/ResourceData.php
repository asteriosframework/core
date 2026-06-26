<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class ResourceData extends Data
{
    public function __construct(
        public string $name,
        public string $url,
    ) {
    }
}
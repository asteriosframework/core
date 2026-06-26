<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class ImageGalleryData extends Data
{
    public function __construct(
        public ResourceData $resource,
        public array $images = []
    ) {
    }
}
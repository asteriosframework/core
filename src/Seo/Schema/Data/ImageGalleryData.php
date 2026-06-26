<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Data;

use Asterios\Core\Data;

final class ImageGalleryData extends Data
{
    public ResourceData $resource;

    /**
     * @var ImageData[]
     */
    public array $images = [];
}
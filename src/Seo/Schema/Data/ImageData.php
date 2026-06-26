<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class ImageData extends Data
{
    public function __construct(
        public string $url,
        public ?string $title = null,
        public ?string $description = null,
        public ?int $width = null,
        public ?int $height = null,
    ) {
    }
}
<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Node;

use Asterios\Core\Seo\Schema\Contracts\Node;
use Asterios\Core\Seo\Schema\Data\ImageData;
use Asterios\Core\Seo\Schema\Data\ImageGalleryData;
use Asterios\Core\Seo\Schema\Enums\SchemaIdEnum;

final readonly class ImageGallery implements Node
{
    public function __construct(
        private ImageGalleryData $data,
    ) {
    }

    public function build(): array
    {
        return [
            '@type' => 'ImageGallery',
            '@id' => $this->data->resource->url . SchemaIdEnum::IMAGE_GALLERY,
            'url' => $this->data->resource->url,
            'name' => $this->data->resource->name,
            'associatedMedia' => $this->associatedMedia(),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function associatedMedia(): array
    {
        return array_map(
            $this->image(...),
            $this->data->images,
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function image(ImageData $image): array
    {
        $data = [
            '@type' => 'ImageObject',
            'url' => $image->url,
            'contentUrl' => $image->url,
        ];

        if ($image->title !== null) {
            $data['name'] = $image->title;
        }

        if ($image->description !== null) {
            $data['caption'] = $image->description;
        }

        if ($image->width !== null) {
            $data['width'] = $image->width;
        }

        if ($image->height !== null) {
            $data['height'] = $image->height;
        }

        return $data;
    }
}
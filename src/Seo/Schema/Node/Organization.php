<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Node;

use Asterios\Core\Seo\Schema\Enums\Contracts\Node;
use Asterios\Core\Seo\Schema\Enums\Data\OrganizationData;

final readonly class Organization implements Node
{
    public function __construct(
        private OrganizationData $data,
    ) {
    }

    public function build(): array
    {
        $organization = [
            '@type' => 'Organization',
            'name' => $this->data->name,
            'url' => $this->data->url,
        ];

        if ($this->data->email !== null) {
            $organization['email'] = $this->data->email;
        }

        if ($this->data->telephone !== null) {
            $organization['telephone'] = $this->data->telephone;
        }

        if ($this->data->logo !== null) {
            $organization['logo'] = [
                '@type' => 'ImageObject',
                'url' => $this->data->logo,
            ];
        }

        return $organization;
    }
}
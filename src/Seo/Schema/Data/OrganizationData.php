<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Data;

use Asterios\Core\Data;

final class OrganizationData extends Data
{
    public function __construct(
        public string $name,
        public string $url,
        public ?string $email = null,
        public ?string $telephone = null,
        public ?string $logo = null,
    ) {
    }
}
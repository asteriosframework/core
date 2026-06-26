<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema\Enums\Contracts;

interface Node
{
    /**
     * Build the schema node.
     *
     * @return array<string, mixed>
     */
    public function build(): array;
}
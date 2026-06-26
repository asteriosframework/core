<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema;

use Asterios\Core\Seo\Schema\Enums\Contracts\Node;

final class Graph
{
    /**
     * @var list<Node>
     */
    private array $nodes = [];

    public function add(Node $node): self
    {
        $this->nodes[] = $node;

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function build(): array
    {
        return array_map(
            static fn(Node $node): array => $node->build(),
            $this->nodes
        );
    }
}
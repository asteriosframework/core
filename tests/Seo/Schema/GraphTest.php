<?php

declare(strict_types=1);

namespace Asterios\Test\Seo\Schema;

use Asterios\Core\Seo\Schema\Contracts\Node;
use Asterios\Core\Seo\Schema\Graph;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class GraphTest extends TestCase
{
    public function test_build_returns_empty_graph(): void
    {
        $graph = new Graph();

        $this->assertSame([], $graph->build());
    }

    public function test_add_appends_node(): void
    {
        $node = m::mock(Node::class);

        $node->shouldReceive('build')
            ->once()
            ->andReturn([
                '@type' => 'Organization',
            ]);

        $graph = new Graph();

        $graph->add($node);

        $this->assertSame([
            [
                '@type' => 'Organization',
            ],
        ], $graph->build());
    }

    public function test_add_returns_graph_instance(): void
    {
        $graph = new Graph();

        $node = m::mock(Node::class);

        $this->assertSame(
            $graph,
            $graph->add($node)
        );
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
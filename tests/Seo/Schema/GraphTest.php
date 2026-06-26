<?php

declare(strict_types=1);

namespace Asterios\Test\Seo\Schema;

use Asterios\Core\Seo\Schema\Graph;
use PHPUnit\Framework\TestCase;

final class GraphTest extends TestCase
{
    public function testBuildReturnsEmptyArray(): void
    {
        $graph = new Graph();

        $this->assertSame([], $graph->build());
    }

    public function testAddReturnsGraphInstance(): void
    {
        $graph = new Graph();

        $this->assertSame(
            $graph,
            $graph->add(new DummyNode())
        );
    }

    public function testBuildReturnsAddedNodes(): void
    {
        $graph = new Graph();

        $graph->add(new DummyNode());

        $this->assertSame([
            [
                '@type' => 'Dummy',
                'name' => 'Dummy Node',
            ],
        ], $graph->build());
    }

    public function testBuildReturnsNodesInCorrectOrder(): void
    {
        $graph = new Graph();

        $graph
            ->add(new DummyNode())
            ->add(new DummyNode());

        $this->assertCount(
            2,
            $graph->build()
        );
    }
}
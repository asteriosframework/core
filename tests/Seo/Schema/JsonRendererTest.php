<?php

declare(strict_types=1);

namespace Asterios\Test\Seo\Schema;

use Asterios\Core\Seo\Schema\JsonRenderer;
use PHPUnit\Framework\TestCase;

class JsonRendererTest extends TestCase
{
    public function test_render_returns_valid_jsonld(): void
    {
        $renderer = new JsonRenderer();

        $json = $renderer->render([
            [
                '@type' => 'Organization',
            ],
        ]);

        $this->assertStringContainsString(
            '<script type="application/ld+json">',
            $json
        );

        $this->assertStringContainsString(
            '"@context": "https://schema.org"',
            $json
        );

        $this->assertStringContainsString(
            '"@graph"',
            $json
        );
    }
}
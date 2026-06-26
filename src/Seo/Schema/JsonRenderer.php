<?php

declare(strict_types=1);

namespace Asterios\Core\Seo\Schema;

use Asterios\Core\Seo\Schema\Enums\Exceptions\RendererException;

final class JsonRenderer
{
    /**
     * @param list<array<string,mixed>> $graph
     * @return string
     * @throws RendererException
     */
    public function render(array $graph): string
    {
        try {
            return sprintf(
                '<script type="application/ld+json">%s</script>',
                json_encode([
                    '@context' => 'https://schema.org',
                    '@graph' => $graph,
                ],
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE)
            );
        } catch (\JsonException $e) {
            throw new RendererException('Failed to render JSON-LD', previous: $e);
        }
    }
}
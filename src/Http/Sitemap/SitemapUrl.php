<?php declare(strict_types=1);

namespace Asterios\Core\Http\Sitemap;

final readonly class SitemapUrl
{
    public function __construct(
        public string $loc,
        public ?\DateTimeInterface $lastMod = null,
        public ?string $changeFreq = null,
        public ?float $priority = null
    ) {
    }
}

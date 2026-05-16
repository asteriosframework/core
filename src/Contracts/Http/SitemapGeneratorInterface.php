<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Http;

use Asterios\Core\Exception\Http\Sitemap\SitemapException;

interface SitemapGeneratorInterface
{
    /**
     * @return self
     */
    public function crawl(): self;

    /**
     * @param string $path
     * @return void
     */
    public function save(string $path): void;

    /**
     * @return string
     * @throws SitemapException
     */
    public function toXml(): string;
}

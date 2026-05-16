<?php

declare(strict_types=1);

namespace Asterios\Core\Http\Sitemap;

use Asterios\Core\Contracts\Http\SitemapGeneratorInterface;
use Asterios\Core\Exception\Http\Sitemap\SitemapException;
use Asterios\Core\Request;
use Asterios\Core\Response;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use DOMDocument;
use DOMXPath;

final class SitemapGenerator implements SitemapGeneratorInterface
{
    private const string XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    private const array ALLOWED_CONTENT_TYPES = [
        'text/html',
        'application/xhtml+xml',
    ];

    private const array IGNORED_EXTENSIONS = [
        'jpg','jpeg','png','gif','webp','svg','ico','bmp',
        'pdf','zip','rar','7z','tar','gz',
        'mp3','mp4','avi','mov','wmv','mkv','webm',
        'css','js','json','xml','txt','csv',
        'woff','woff2','ttf','eot',
    ];

    private string $host;
    private string $scheme;
    private array $visited = [];
    private array $entries = [];
    private array $robotsRules = [];
    private Request $request;

    public function __construct(
        private readonly string $startUrl,
        private readonly int $maxDepth = 5,
        private readonly int $maxUrls = 1000,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 5,
        private readonly string $userAgent = 'Asterios Sitemap Generator',
        private readonly string $robotsUserAgent = '*'
    ) {
        if (!filter_var($startUrl, FILTER_VALIDATE_URL))
        {
            throw new \InvalidArgumentException('Invalid start URL.');
        }

        $parts = parse_url($startUrl);

        if (!isset($parts['scheme'], $parts['host']))
        {
            throw new \InvalidArgumentException('Unable to parse start URL.');
        }

        $this->scheme = strtolower($parts['scheme']);
        $this->host = strtolower($parts['host']);

        $this->request = new Request();
        $this->request->user_agent = $this->userAgent;
        $this->request->options = [
            'CONNECTTIMEOUT' => $this->connectTimeout,
            'TIMEOUT' => $this->timeout,
        ];
    }

    /**
     * @inheritDoc
     */
    public function crawl(): self
    {
        $this->loadRobotsTxt();
        $this->crawlUrl($this->normalizeUrl($this->startUrl), 0);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save(string $path): void
    {
        $directory = dirname($path);

        if (!is_dir($directory))
        {
            if (!mkdir($directory, 0755, true) && !is_dir($directory))
            {
                throw new SitemapException(sprintf('Directory "%s" was not created', $directory));
            }
        }

        file_put_contents($path, $this->toXml());
    }

    /**
     * @inheritDoc
     */
    public function toXml(): string
    {
        try
        {

            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;

            $urlSet = $dom->createElement('urlset');
            $urlSet->setAttribute('xmlns', self::XMLNS);

            foreach ($this->entries as $url => $lastMod)
            {
                $urlNode = $dom->createElement('url');
                $urlNode->appendChild($dom->createElement('loc', $url));
                $urlNode->appendChild($dom->createElement('lastmod', $lastMod->format(DateTimeInterface::ATOM)));
                $urlSet->appendChild($urlNode);
            }

            $dom->appendChild($urlSet);

            return (string) $dom->saveXML();

        }
        catch (\DOMException $e)
        {
            throw new SitemapException($e->getMessage());
        }
    }

    /**
     * @param string $url
     * @param int $depth
     * @return void
     * @throws SitemapException
     */
    private function crawlUrl(string $url, int $depth): void
    {
        if ($depth > $this->maxDepth)
        {
            return;
        }

        if (count($this->entries) >= $this->maxUrls)
        {
            return;
        }

        if (isset($this->visited[$url]))
        {
            return;
        }

        if (!$this->isAllowedByRobots($url))
        {
            return;
        }

        $this->visited[$url] = true;

        $response = $this->request->get($url);

        if (!$response instanceof Response)
        {
            return;
        }

        if (!$this->isSuccessfulResponse($response))
        {
            return;
        }

        $contentType = $this->getHeader($response, 'Content-Type');

        if (!$this->isHtmlContentType($contentType))
        {
            return;
        }

        $this->entries[$url] = $this->resolveLastModified($response);

        foreach ($this->extractLinks($response->body, $url) as $link)
        {
            if (count($this->entries) >= $this->maxUrls)
            {
                break;
            }

            $this->crawlUrl($link, $depth + 1);
        }
    }

    /**
     * @param string $html
     * @param string $baseUrl
     * @return array
     */
    private function extractLinks(string $html, string $baseUrl): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//a[@href]');

        $links = [];

        if ($nodes === false)
        {
            return [];
        }

        foreach ($nodes as $node)
        {
            $href = trim((string) $node->getAttribute('href'));

            if ($href === '')
            {
                continue;
            }

            $normalized = $this->normalizeUrl($href, $baseUrl);

            if ($normalized === null)
            {
                continue;
            }

            if (!$this->isSameHost($normalized))
            {
                continue;
            }

            if ($this->isIgnoredResource($normalized))
            {
                continue;
            }

            $links[$normalized] = true;
        }

        return array_keys($links);
    }

    /**
     * @param string $url
     * @param string|null $baseUrl
     * @return string|null
     */
    private function normalizeUrl(string $url, ?string $baseUrl = null): ?string
    {
        if ($this->hasIgnoredScheme($url))
        {
            return null;
        }

        if (str_starts_with($url, '//'))
        {
            $url = $this->scheme . ':' . $url;
        }
        elseif (!filter_var($url, FILTER_VALIDATE_URL))
        {
            $baseUrl ??= $this->startUrl;
            $url = $this->resolveRelativeUrl($baseUrl, $url);
        }

        $parts = parse_url($url);

        if ($parts === false || !isset($parts['scheme'], $parts['host']))
        {
            return null;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true))
        {
            return null;
        }

        $path = $parts['path'] ?? '/';
        $path = $this->normalizePath($path);

        return strtolower($parts['scheme']) . '://' . strtolower($parts['host']) . $path;
    }

    /**
     * @param string $baseUrl
     * @param string $relative
     * @return string
     */
    private function resolveRelativeUrl(string $baseUrl, string $relative): string
    {
        $base = parse_url($baseUrl);
        $scheme = $base['scheme'];
        $host = $base['host'];
        $basePath = $base['path'] ?? '/';

        if (str_starts_with($relative, '/'))
        {
            return $scheme . '://' . $host . $relative;
        }

        $directory = rtrim(dirname($basePath), '/');

        return $scheme . '://' . $host . ($directory) . '/' . $relative;
    }

    private function normalizePath(string $path): string
    {
        $segments = explode('/', $path);
        $resolved = [];

        foreach ($segments as $segment)
        {
            if ($segment === '' || $segment === '.')
            {
                continue;
            }

            if ($segment === '..')
            {
                array_pop($resolved);
                continue;
            }

            $resolved[] = $segment;
        }

        return '/' . implode('/', $resolved);
    }

    /**
     * @param string $url
     * @return bool
     */
    private function hasIgnoredScheme(string $url): bool
    {
        foreach (['mailto:', 'tel:', 'javascript:', 'data:'] as $scheme)
        {
            if (str_starts_with(strtolower($url), $scheme))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @return bool
     */
    private function isSameHost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return strtolower((string) $host) === $this->host;
    }

    /**
     * @param string $url
     * @return bool
     */
    private function isIgnoredResource(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path))
        {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === '')
        {
            return false;
        }

        return in_array($extension, self::IGNORED_EXTENSIONS, true);
    }

    /**
     * @param Response $response
     * @return bool
     */
    private function isSuccessfulResponse(Response $response): bool
    {
        return ($response->headers['Status-Code'] ?? null) === '200';
    }

    /**
     * @param string|null $contentType
     * @return bool
     */
    private function isHtmlContentType(?string $contentType): bool
    {
        if ($contentType === null)
        {
            return false;
        }

        $mime = strtolower(trim(explode(';', $contentType)[0]));

        return in_array($mime, self::ALLOWED_CONTENT_TYPES, true);
    }

    /**
     * @param Response $response
     * @return DateTimeImmutable
     * @throws SitemapException
     */
    private function resolveLastModified(Response $response): DateTimeImmutable
    {
        try
        {
            $header = $this->getHeader($response, 'Last-Modified');

            if ($header !== null)
            {
                return new DateTimeImmutable($header);
            }

            return new DateTimeImmutable('now', new DateTimeZone('UTC'));
        }
        catch (\Exception $e)
        {
            throw new SitemapException($e->getMessage());
        }
    }

    /**
     * @param Response $response
     * @param string $header
     * @return string|null
     */
    private function getHeader(Response $response, string $header): ?string
    {
        foreach ($response->headers as $key => $value)
        {
            if (strcasecmp($key, $header) === 0)
            {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * @return void
     */
    private function loadRobotsTxt(): void
    {
        $robotsUrl = $this->scheme . '://' . $this->host . '/robots.txt';
        $response = $this->request->get($robotsUrl);

        if (!$response instanceof Response)
        {
            return;
        }

        if (($response->headers['Status-Code'] ?? null) !== '200')
        {
            return;
        }

        $this->parseRobotsTxt($response->body);
    }

    /**
     * @param string $content
     * @return void
     */
    private function parseRobotsTxt(string $content): void
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $active = false;

        foreach ($lines as $line)
        {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#'))
            {
                continue;
            }

            if (stripos($line, 'User-agent:') === 0)
            {
                $agent = trim(substr($line, 11));
                $active = $agent === '*' || strcasecmp($agent, $this->robotsUserAgent) === 0;
                continue;
            }

            if (!$active)
            {
                continue;
            }

            if (stripos($line, 'Disallow:') === 0)
            {
                $path = trim(substr($line, 9));

                if ($path !== '')
                {
                    $this->robotsRules[] = $path;
                }
            }
        }
    }

    /**
     * @param string $url
     * @return bool
     */
    private function isAllowedByRobots(string $url): bool
    {
        if ($this->robotsRules === [])
        {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        foreach ($this->robotsRules as $rule)
        {
            if (str_starts_with($path, $rule))
            {
                return false;
            }
        }

        return true;
    }
}

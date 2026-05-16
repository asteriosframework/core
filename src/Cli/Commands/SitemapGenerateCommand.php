<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Enum\CliStatusIcon;
use Asterios\Core\Http\Sitemap\SitemapGenerator;

#[Command(
    name: 'sitemap:generate',
    description: 'Crawl a website and generate an XML sitemap.',
    group: 'SEO',
    aliases: ['--sm'],
    options: [
        '--url' => 'Target website URL (required)',
        '--maxDepth' => 'Maximum crawl depth (Default: 5)',
        '--maxUrls' => 'Maximum URLs to crawl (Default: 1000)',
        '--timeout' => 'HTTP request timeout in seconds (Default: 30)',
        '--connectTimeout' => 'Connection timeout in seconds (Default: 5)',
        '--output' => 'Sitemap output file path (Default: storage/sitemap.xml)',
        '--help' => 'Show command help',
    ]
)]
final class SitemapGenerateCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        if ($this->hasFlag('--help'))
        {
            $this->printCommandHelpFromAttribute();

            return;
        }

        $url = $this->getValue('--url');

        if ($url === null || trim($url) === '')
        {
            echo CliStatusIcon::Error->icon() . ' Missing required option: --url=https://example.com' . PHP_EOL;

            return;
        }

        $maxDepth = (int) ($this->getValue('--maxDepth')  ?? 5);
        $maxUrls = (int) ($this->getValue('--maxUrls') ?? 1000);
        $timeout = (int) ($this->getValue('--timeout') ?? 30);
        $connectTimeout = (int) ($this->getValue('--connectTimeout') ?? 5);
        $output = $this->getValue('--output') ?? 'storage/sitemap/sitemap.xml';

        if ($maxDepth < 1 || $maxUrls < 1 || $timeout < 1 || $connectTimeout < 1)
        {
            echo CliStatusIcon::Error->icon() . ' Invalid numeric configuration values.' . PHP_EOL;

            return;
        }

        echo CliStatusIcon::Pending->icon() . ' Crawling: ' . $url . PHP_EOL;
        echo CliStatusIcon::Pending->icon() . ' Max depth: ' . $maxDepth . PHP_EOL;
        echo CliStatusIcon::Pending->icon() . ' Max URLs: ' . $maxUrls . PHP_EOL;
        echo CliStatusIcon::Pending->icon() . ' Timeout: ' . $timeout . 's' . PHP_EOL;
        echo CliStatusIcon::Pending->icon() . ' Connect timeout: ' . $connectTimeout . 's' . PHP_EOL;
        echo CliStatusIcon::Pending->icon() . ' Output: ' . $output . PHP_EOL;
        echo PHP_EOL;

        try
        {
            $generator = new SitemapGenerator(startUrl: $url, maxDepth: $maxDepth, maxUrls: $maxUrls, timeout: $timeout, connectTimeout: $connectTimeout);

            $generator->crawl();
            $generator->save($output);

            echo CliStatusIcon::Success->icon() . ' Sitemap generated successfully.' . PHP_EOL;

            echo CliStatusIcon::Success->icon() . ' Saved to: ' . $output . PHP_EOL;
        }
        catch (\Throwable $exception)
        {
            echo CliStatusIcon::Error->icon() . ' Sitemap generation failed: ' . $exception->getMessage() . PHP_EOL;
        }
    }
}
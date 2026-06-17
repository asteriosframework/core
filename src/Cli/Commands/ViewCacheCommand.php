<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Env;
use Asterios\Core\Exception\TwigTemplateManagerException;
use Asterios\Core\Exception\ViewNamespaceLoaderException;
use Asterios\Core\View\Twig\TwigManager;

#[Command(
    name: 'view:cache',
    description: 'Warmup Twig template cache',
    group: 'View',
    options: [
        '--help' => 'Show command help',
    ],
)]
class ViewCacheCommand  extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        if ($this->hasFlag('--help'))
        {
            $this->printCommandHelpFromAttribute();

            return;
        }

        $env = new Env(Asterios::getBasePath() . '/.env');

        try
        {
            $twig = TwigManager::getTwig($env);
            TwigManager::warmupCache($twig);

            $this->success('Twig cache warmed up.');
        } catch (TwigTemplateManagerException|ViewNamespaceLoaderException $e)
        {
            $this->error('Twig cache warmed up failed: '.$e->getMessage());
        }
    }
}

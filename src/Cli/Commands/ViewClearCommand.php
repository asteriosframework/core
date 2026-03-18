<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Env;
use Asterios\Core\Exception\TwigTemplateManagerException;
use Asterios\Core\View\Twig\TwigManager;

#[Command(
name: 'view:clear',
description: 'Clear Twig template cache',
group: 'View'
)]
class ViewClearCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function handle(?string $argument): void
    {
        $env = new Env(Asterios::getBasePath() . '/.env');
        try {
            TwigManager::clearCache($env);

            $this->success('Twig cache cleared.');
        } catch (TwigTemplateManagerException $e) {
            $this->error('Twig cache clear failed: '.$e->getMessage());
        }
    }
}

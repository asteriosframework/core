<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
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
        TwigManager::clearCache();

        $this->success('Twig cache cleared.');
    }
}

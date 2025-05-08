<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;

#[Command(
    name: 'about',
    description: 'Display information about AsteriosPHP',
    group: 'System',
    aliases: ['--info']
)]
class AboutCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function handle(?string $argument): void
    {
        $this->printHeader();
        $this->printDataTable([
            'System' => [
                'PHP Version' => PHP_VERSION,
                'Framework Version' => Asterios::VERSION,
                'Environment' => Asterios::getEnvironment(),
                'Encoding' => Asterios::getEncoding(),
                'Timezone' => Asterios::getTimezone(),
            ],
        ]);
    }
}
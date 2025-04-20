<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'about',
    description: 'Display information about AsteriosPHP',
    group: 'System',
    aliases: ['--info']
)]
class AboutCommand implements CommandInterface
{
    use CommandsBuilderTrait;

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
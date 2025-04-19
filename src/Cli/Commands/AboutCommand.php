<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Interfaces\CommandInterface;

class AboutCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    /**
     * @inheritDoc
     */
    public function handle(?string $argument): void
    {
        $rows = [
            'Asterios Version' => Asterios::VERSION,
            'Environment' => ucfirst(Asterios::getEnvironment()),
            'Timezone' => Asterios::getTimezone(),
            'Encoding' => Asterios::getEncoding(),
            'PHP Version' => PHP_VERSION,
        ];

        $this->printHeader();
        echo "Environment\n";
        $this->printTable($rows, '');
        echo "Available commands\n";
        $this->printTable($this->commands());
    }

    /**
     * @inheritDoc
     */
    public static function description(): string
    {
        return 'Information about the Asterios PHP Framework';
    }
}
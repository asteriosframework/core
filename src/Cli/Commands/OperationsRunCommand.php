<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Enum\CliStatusIcon;
use Asterios\Core\Operation\Operation;

#[Command(
    name: 'operations:run',
    description: 'Run all pending operations',
    group: 'Operations',
    aliases: ['--opr'],
    options: [
        '--force' => 'Re-run already executed operations',
        '--help' => 'Show command help',
    ]
)]
final class OperationsRunCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        if ($this->hasFlag('--help'))
        {
            $this->printCommandHelpFromAttribute();

            return;
        }

        $operation = $this->createOperation();

        if ($this->hasFlag('--force'))
        {
            $operation->force();
        }

        $operation->execute();

        /** @var array $messages */
        $messages = $operation->getMessages();

        foreach ($messages as $operationMessage)
        {
            foreach ($operationMessage as $filename => $status)
            {
                $status = match ($status)
                {
                    'done'
                    => CliStatusIcon::Success->icon()
                        . 'Executed',

                    'skipped'
                    => CliStatusIcon::Warning->icon()
                        . 'Skipped operation',

                    'failed'
                    => CliStatusIcon::Error->icon()
                        . 'Operation failed',

                    default
                    => CliStatusIcon::Unknown->icon()
                        . 'Operation in unknown state',
                };

                echo $status
                    . ' '
                    . $filename
                    . PHP_EOL;
            }
        }
    }

    /** @codeCoverageIgnoreStart */
    protected function createOperation(): Operation
    {
        return new Operation();
    }
    /** @codeCoverageIgnoreEnd */
}

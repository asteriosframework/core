<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Enum\CliStatusIcon;
use Asterios\Core\Operation\Operation;

#[Command(
    name: 'operations:status',
    description: 'Show operation execution status',
    group: 'Operations',
    aliases: ['--ops']
)]
final class OperationsStatusCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        $operation = $this->createOperation();

        $executedOperations = $operation->getRanOperations();
        $allOperationFiles = $operation->getAllOperationFiles();

        if (empty($allOperationFiles))
        {
            echo CliStatusIcon::Warning->icon() . 'No operations were found.' . PHP_EOL;

            return;
        }

        foreach ($allOperationFiles as $file)
        {
            $operationName = basename(
                $file,
                '.php'
            );

            $executed = $operation->hasExecuted(
                $executedOperations,
                $operationName
            );

            $status = $executed
                ? CliStatusIcon::Success->icon() . 'Executed'
                : CliStatusIcon::Warning->icon() . 'Pending';

            echo $status
                . ' '
                . $operationName
                . PHP_EOL;
        }
    }

    /** @codeCoverageIgnoreStart */
    protected function createOperation(): Operation
    {
        return new Operation();
    }
    /** @codeCoverageIgnoreEnd */
}
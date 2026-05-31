<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Enum\CliStatusIcon;
use Asterios\Core\Operation\Operation;

#[Command(
    name: 'operations:status',
    description: 'Show the status of all operations (executed/pending)',
    group: 'Operations',
    aliases: ['--ops']
)]
final class OperationsStatusCommand extends BaseCommand
{
    private Operation $operation;

    public function __construct(?Operation $operation = null)
    {
        parent::__construct();

        $this->operation = $operation ?? new Operation();
    }

    public function handle(?string $argument): void
    {
        $this->printHeader();

        $executedOperations = $this->operation->getRanOperations();
        $allOperationFiles = $this->operation->getAllOperationFiles();

        $statusList = [];

        foreach ($allOperationFiles as $operationFile)
        {
            $operationName = pathinfo(
                $operationFile,
                PATHINFO_FILENAME
            );

            $isExecuted = $this->operation->hasExecuted(
                $executedOperations,
                $operationName
            );

            $statusList[] = [
                'Status' => $isExecuted
                    ? CliStatusIcon::Success->icon() . 'Executed'
                    : CliStatusIcon::Pending->icon() . 'Pending',
                'Operation' => $operationName,
            ];
        }

        if ([] === $statusList)
        {
            echo CliStatusIcon::Warning->icon()
                . 'No operations were found.'
                . PHP_EOL;

            return;
        }

        $this->printListTable(
            'Operation Status',
            $statusList,
            'Status',
            'Operation'
        );
    }
}

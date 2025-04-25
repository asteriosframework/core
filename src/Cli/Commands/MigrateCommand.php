<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Db\Migration;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'migrate',
    description: 'Run all outstanding migrations',
    group: 'Database'
)]
class MigrateCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    public function handle(?string $argument): void
    {
        $this->printHeader();

        $migration = new Migration();
        $executed = $migration->migrate(); // Gibt hoffentlich ein Array oder Resultat zurück

        $messages = $migration->getMessages();

        foreach ($messages as $message)
        {
            echo $message . "\n";
            var_dump($message);
        }

    }
}

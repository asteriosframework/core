<?php

declare(strict_types=1);

namespace Asterios\Core\Commands;

use Symfony\Component\Console\Command\Command;
use Asterios\Core\Commands\Support\BaseCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'about', description: 'Show information about the framework installation')]
class About extends BaseCommand
{
    protected function configure(): void
    {
        $this->setHelp('Show information about the framework installation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $io = new SymfonyStyle($input, $output);

        $io->definitionList(
            'AsteriosPHP Framework',
            '',
            ['Core Packages' => 'Version'],
            new TableSeparator(),
            ['asterios/core' => $this->getCoreVersion()],
        );

        return Command::SUCCESS;
    }

    protected function getCoreVersion(): string
    {
        $cmd = '($(which git 2>&1 1>/dev/null) && (git describe 2>/dev/null || echo "$(git branch --show-current) ($(git rev-parse --short HEAD))")) || echo ""';
        $version = @shell_exec($cmd);

        if (false !== $version && null !== $version)
        {
            return $version;
        }

        return '';
    }
}
<?php

declare(strict_types=1);

namespace Asterios\Core\Commands\Support;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    protected function setSuccessStyle(OutputInterface $output): void
    {
        $outputStyle = new OutputFormatterStyle(
            foreground: 'black',
            background: 'green'
        );
        $output->getFormatter()->setStyle('success', $outputStyle);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setSuccessStyle($output);

        return Command::SUCCESS;
    }
}
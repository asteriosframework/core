<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Cli\Commands\ListCommand;
use Asterios\Core\Contracts\Cli\CommandRegistryInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class ListCommandTest extends MockeryTestCase
{
    public function testHandlePrintsHeaderAndTable(): void
    {
        $fakeCommands = [
            [
                'name' => 'make:model',
                'description' => 'Create a new model',
                'group' => 'Make',
                'aliases' => ['--mm']
            ],
            [
                'name' => 'migrate:status',
                'description' => 'Show migration status',
                'group' => 'Database',
                'aliases' => ['--mi']
            ]
        ];

        $registryMock = m::mock(CommandRegistryInterface::class);
        $registryMock->shouldReceive('all')->andReturn($fakeCommands);

        $command = new class ($registryMock) extends ListCommand {
            public function __construct(CommandRegistryInterface $registry)
            {
                parent::__construct();
                $this->setCommandRegistry($registry);
            }
        };

        ob_start();
        try
        {
            $command->handle(null);
            $output = ob_get_contents();
        }
        finally
        {
            ob_end_clean();
        }

        self::assertStringContainsString('CLI', $output);
        self::assertStringContainsString('Make:', $output);
        self::assertStringContainsString('make:model', $output);
        self::assertStringContainsString('migrate:status', $output);
    }
}

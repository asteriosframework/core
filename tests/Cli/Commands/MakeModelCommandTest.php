<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Cli\Commands\MakeModelCommand;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MakeModelCommandTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    protected function getCommandWithArgs(array $args): MakeModelCommand
    {
        /** @var MakeModelCommand|m\MockInterface $makeModelCommandMock */
        $makeModelCommandMock = m::mock(MakeModelCommand::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $makeModelCommandMock->setArgs($args);
        $makeModelCommandMock->shouldReceive('printHeader')->once();

        return $makeModelCommandMock;
    }

    public function testModelCreatedWithDefaultNamespace(): void
    {
        /** @var MakeModelCommand|m\MockInterface $command */
        $command = $this->getCommandWithArgs(['User']);

        $expectedDir = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']) . 'app/Models/';
        $expectedFile = $expectedDir . 'User.php';

        $command->shouldReceive('fileExists')->once()->with($expectedFile)->andReturn(false);
        $command->shouldReceive('ensureDirectoryExists')->once()->with($expectedDir);
        $command->shouldReceive('writeFile')->once()->with($expectedFile, 'User');

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertStringContainsString("✅", $output);
        self::assertStringContainsString("User", $output);
    }

    public function testModelCreatedWithCustomNamespace(): void
    {
        /** @var MakeModelCommand|m\MockInterface $command */

        $command = $this->getCommandWithArgs(['User', '--namespace=My\\Custom\\Models']);

        $expectedDir = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']) . 'app/Models/';
        $expectedFile = $expectedDir . 'User.php';

        $command->shouldReceive('fileExists')->once()->with($expectedFile)->andReturn(false);
        $command->shouldReceive('ensureDirectoryExists')->once()->with($expectedDir);
        $command->shouldReceive('writeFile')->once()->with($expectedFile, 'User');

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertStringContainsString("My\\Custom\\Models\\User", $output);
    }

    public function testHandleWithMissingArgumentShowsError(): void
    {
        /** @var MakeModelCommand|m\MockInterface $command */

        $command = $this->getCommandWithArgs([]);

        $command->shouldReceive('printError')->once()->with('Missing model name.');
        $command->shouldNotReceive('ensureDirectoryExists');
        $command->shouldNotReceive('writeFile');

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertStringContainsString('Example: asterios make:model', $output);
    }

    public function testExistingModelSkipsGeneration(): void
    {
        /** @var MakeModelCommand|m\MockInterface $command */

        $command = $this->getCommandWithArgs(['User']);

        $expectedDir = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']) . 'app/Models/';
        $expectedFile = $expectedDir . 'User.php';

        $command->shouldReceive('fileExists')->once()->with($expectedFile)->andReturn(true);
        $command->shouldReceive('ensureDirectoryExists')->once()->with($expectedDir);
        $command->shouldNotReceive('writeFile');

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertStringContainsString('already exists', $output);
    }
}

<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Cli\Commands\MakeModelCommand;
use Asterios\Core\Cli\Commands\MakeSeederCommand;
use Asterios\Core\Env;
use Asterios\Core\Execution\PathResolver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MakeSeederCommandTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testHandleWithoutArgumentPrintsError(): void
    {
        /** @var MakeModelCommand|m\MockInterface $command */
        $command = m::mock(MakeSeederCommand::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('printError')->once()->with('Missing seeder name.');

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertStringContainsString("Example: asterios make:seeder users", $output);
    }

    public function testHandleWithExistingSeederPrintsWarning(): void
    {
        $seederName = 'users';
        $seederPath = '/fake/path/';

        /** @var MakeModelCommand|m\MockInterface $command */
        $command = m::mock(MakeSeederCommand::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('fileExists')->once()->andReturn(true);
        $command->shouldReceive('writeFile')->never();

        ob_start();
        $command->handle($seederName);
        $output = ob_get_clean();

        self::assertStringContainsString("⚠️  Seeder '$seederName' already exists.", $output);
    }

    public function testHandleCreatesSeederFileSuccessfully(): void
    {
        $seederName = 'users123';
        $seederPath = '/fake/path/';
        $expectedFile = $seederPath . $seederName . '.json';

        $pathResolverMock = m::mock(new PAthResolver(new Env()));
        $pathResolverMock->shouldReceive('resolve')->andReturn($seederPath);


        /** @var MakeModelCommand|m\MockInterface $command */
        $command = m::mock(MakeSeederCommand::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('fileExists')->once()->andReturn(false);
        $command->shouldReceive('writeFile')->once()->andReturnTrue();

        ob_start();
        $command->handle($seederName);
        $output = ob_get_clean();

        self::assertStringContainsString("✅  Seeder '$seederName' created.", $output);
    }
}

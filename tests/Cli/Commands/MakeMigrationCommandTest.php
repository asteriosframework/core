<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Cli\Commands\MakeMigrationCommand;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MakeMigrationCommandTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testCreateTableAndHandleWithArgumentCallsFileMethods(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/project/public';

        $command = m::mock(MakeMigrationCommand::class)->makePartial();
        $command->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('ensureDirectoryExists')
            ->once()
            ->with(m::on(fn ($arg) => str_contains($arg, 'database/migrations')));

        $command->shouldReceive('writeFile')
            ->once()
            ->withArgs(function ($path, $content) {
                return str_contains($path, 'create_users_table') &&
                    str_contains($content, "Schema::create('users'");
            });

        ob_start();
        $command->handle('create_users_table');
        ob_end_clean();
    }

    public function testUpdateTableAndHandleWithArgumentCallsFileMethods(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/project/public';

        $command = m::mock(MakeMigrationCommand::class)->makePartial();
        $command->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('ensureDirectoryExists')
            ->once()
            ->with(m::on(fn ($arg) => str_contains($arg, 'database/migrations')));

        $command->shouldReceive('writeFile')
            ->once()
            ->withArgs(function ($path, $content) {
                return str_contains($path, 'update_users_table') &&
                    str_contains($content, "Schema::table('users'");
            });

        ob_start();
        $command->handle('update_users_table');
        ob_end_clean();
    }

    public function testAlterTableAndHandleWithArgumentCallsFileMethods(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/project/public';

        $command = m::mock(MakeMigrationCommand::class)->makePartial();
        $command->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('ensureDirectoryExists')
            ->once()
            ->with(m::on(fn ($arg) => str_contains($arg, 'database/migrations')));

        $command->shouldReceive('writeFile')
            ->once()
            ->withArgs(function ($path, $content) {
                return str_contains($path, 'alter_users_table') &&
                    str_contains($content, "");
            });

        ob_start();
        $command->handle('alter_users_table');
        ob_end_clean();
    }

    public function testHandleWithoutArgumentPrintsError(): void
    {
        $command = m::mock(MakeMigrationCommand::class)->makePartial();
        $command->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('printError')->once()->with('Missing migration name.');

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertStringContainsString('Example: asterios make:migration create_users_table', $output);
    }
}


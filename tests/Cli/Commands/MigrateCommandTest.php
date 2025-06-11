<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Cli\Commands\MigrateCommand;
use Asterios\Core\Db\Migration;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MigrateCommandTest extends TestCase
{
    #[DataProvider('migrateCommandProvider')]
    public function testMigrateCommand(string $migrateStatus, bool $forceMigration, string $expectedMessage): void
    {
        $mockMigration = m::mock(Migration::class);
        $mockMigration->shouldReceive('migrate')->once();
        $mockMigration->shouldReceive('getMessages')->andReturn([
            ['2024_01_01_create_users_table.php' => $migrateStatus],
        ]);

        $command = m::mock(MigrateCommand::class)
            ->makePartial();

        $command->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('hasFlag')->with('--help')->andReturn(false);
        $command->shouldReceive('hasFlag')->with('--force')->andReturn($forceMigration);
        $command->shouldReceive('createMigration')->andReturn($mockMigration);

        if ($forceMigration)
        {
            $mockMigration->shouldReceive('force')->once()->andReturnSelf();
        }

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertStringContainsString($expectedMessage, $output);

        m::close();
    }

    public function testMigrateHelpCommand(): void
    {

        $command = m::mock(MigrateCommand::class)
            ->makePartial();

        $command->shouldAllowMockingProtectedMethods();
        $command->shouldReceive('printHeader')->once();
        $command->shouldReceive('hasFlag')->with('--help')->andReturn(true);
        $command->shouldReceive('printCommandHelpFromAttribute')->andReturn('test');

        ob_start();
        $command->handle(null);
        $output = ob_get_clean();

        self::assertEquals('', $output);
    }

    ########## Provider ##########

    public static function migrateCommandProvider(): array
    {
        return [
            ['done', true, 'Migrated'],
            ['skipped', false, 'Skipped migration'],
            ['missing', false, 'Missing method "up" in migration'],
            ['failed', false, 'Migration failed'],
            ['something', false, 'Migration in unknown state'],
        ];
    }
}

<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Cli\Commands\MigrateStatusCommand;
use Asterios\Core\Db\Migration;
use Asterios\Core\Enum\CliStatusIcon;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MigrateStatusCommandTest extends MockeryTestCase
{
    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testHandleWithMigrations(): void
    {
        $mockMigration = m::mock(Migration::class);

        $mockMigration->shouldReceive('getRanMigrations')
            ->once()
            ->andReturn(['2024_01_01_create_users_table']);

        $mockMigration->shouldReceive('getAllMigrationFiles')
            ->once()
            ->andReturn([
                '/database/migrations/2024_01_01_create_users_table.php',
                '/database/migrations/2024_02_01_create_posts_table.php',
            ]);

        $mockMigration->shouldReceive('hasMigrated')
            ->with(['2024_01_01_create_users_table'], '2024_01_01_create_users_table')
            ->once()
            ->andReturn(true);

        $mockMigration->shouldReceive('hasMigrated')
            ->with(['2024_01_01_create_users_table'], '2024_02_01_create_posts_table')
            ->once()
            ->andReturn(false);

        $command = new MigrateStatusCommand($mockMigration);

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

        self::assertStringContainsString(CliStatusIcon::Success->icon() . 'Migrated', $output);
        self::assertStringContainsString(CliStatusIcon::Pending->icon() . 'Pending', $output);
        self::assertStringContainsString('Database Migration Status:', $output);
    }

    public function testHandleWithNoMigrations(): void
    {
        $mockMigration = m::mock(Migration::class);

        $mockMigration->shouldReceive('getRanMigrations')
            ->once()
            ->andReturn([]);

        $mockMigration->shouldReceive('getAllMigrationFiles')
            ->once()
            ->andReturn([]);

        $command = new MigrateStatusCommand($mockMigration);

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

        self::assertStringContainsString('No migrations were found.', $output);
    }
}

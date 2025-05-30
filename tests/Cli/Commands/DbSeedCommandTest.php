<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Cli\Commands\DbSeedCommand;
use Asterios\Core\Db\Seeder;
use Asterios\Core\Enum\CliStatusIcon;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DbSeedCommandTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testHandleWithSeederPrintsExpectedOutput(): void
    {
        $messages = [
            [
                'UsersSeeder.php' => 'done',
                'PostsSeeder.php' => 'failed',
                'CommentsSeeder.php' => 'unknown',
            ],
        ];

        $seederMock = m::mock(Seeder::class);
        $seederMock->shouldReceive('seed')
            ->once();
        $seederMock->shouldReceive('getMessages')
            ->once()
            ->andReturn($messages);

        // DbSeedCommand teil-mocken, um printHeader zu überwachen
        $commandMock = m::mock(DbSeedCommand::class)
            ->makePartial();
        $commandMock->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('printHeader')
            ->once();

        // Output abfangen
        ob_start();
        $commandMock->handleWithSeeder($seederMock);
        $output = ob_get_clean();

        // Erwarteter Output
        $expectedOutput = implode(PHP_EOL, [
                CliStatusIcon::Success->icon() . 'Seeded UsersSeeder.php',
                CliStatusIcon::Error->icon() . 'Seeding failed PostsSeeder.php',
                CliStatusIcon::Unknown->icon() . 'Seeding in unknown state CommentsSeeder.php',
            ]) . PHP_EOL;

        // Assertion
        self::assertSame($expectedOutput, $output);
    }
}

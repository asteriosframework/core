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

        $commandMock = m::mock(DbSeedCommand::class)
            ->makePartial();
        $commandMock->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('getSeeder')->andReturn($seederMock);

        $commandMock->shouldReceive('printHeader')
            ->once();


        ob_start();
        $commandMock->handle(null);
        $output = ob_get_clean();

        $expectedOutput = implode(PHP_EOL, [
                CliStatusIcon::Success->icon() . 'Seeded UsersSeeder.php',
                CliStatusIcon::Error->icon() . 'Seeding failed PostsSeeder.php',
                CliStatusIcon::Unknown->icon() . 'Seeding in unknown state CommentsSeeder.php',
            ]) . PHP_EOL;

        self::assertSame($expectedOutput, $output);
    }
}

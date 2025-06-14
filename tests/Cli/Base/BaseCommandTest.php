<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Base;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Cli\Builder\ColorBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[Command(
    name: 'test:run',
    description: 'This is a test command.',
    options: [
        '--help' => 'Show help',
        '--verbose' => 'Show verbose output',
    ]
)]
class TestCommand extends BaseCommand
{
    public function __construct(private ColorBuilder $mockColor)
    {
        $this->colorBuilder = $mockColor;
    }

    public function handle(?string $argument): void
    {
    }

    public function testPrintHelp(): void
    {
        $this->printCommandHelpFromAttribute();
    }

}

class BaseCommandTest extends MockeryTestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testPrintCommandHelpFromAttribute(): void
    {
        $mockColor = m::mock(ColorBuilder::class);
        $mockColor->shouldReceive('cyan')
            ->andReturnSelf()
            ->times(3);
        $mockColor->shouldReceive('yellow')
            ->andReturnSelf()
            ->once();
        $mockColor->shouldReceive('apply')
            ->andReturnUsing(fn ($text) => "[[$text]]");

        $command = new TestCommand($mockColor);

        ob_start();
        $command->testPrintHelp();
        $output = ob_get_clean();

        self::assertStringContainsString('[[Command:     ]]', $output);
        self::assertStringContainsString('[[test:run]]', $output);
        self::assertStringContainsString('[[Description: ]]', $output);
        self::assertStringContainsString('This is a test command.', $output);
        self::assertStringContainsString('--help', $output);
        self::assertStringContainsString('--verbose', $output);
    }
}

<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Builder;

use Asterios\Core\Cli\Builder\ColorBuilder;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Cli\CommandRegistry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CommandsBuilderTraitTest extends MockeryTestCase
{
    protected ColorBuilder $colorBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->colorBuilder = m::mock(ColorBuilder::class);

        $this->colorBuilder->shouldReceive('magenta')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('cyan')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('red')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('gray')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('green')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('yellow')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('white')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('bold')
            ->andReturnSelf();
        $this->colorBuilder->shouldReceive('apply')
            ->andReturnUsing(fn($text) => "[[$text]]");
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testPrintHeaderOutputsFormattedText(): void
    {
        $instance = new class($this->colorBuilder) {
            use CommandsBuilderTrait;

            public string $output = '';

            public function __construct($builder)
            {
                $this->colorBuilder = $builder;
            }

            public function run(): void
            {
                ob_start();
                $this->printHeader();
                $this->output = ob_get_clean();
            }
        };

        $instance->run();

        $this->assertStringContainsString('Asterios PHP Framework CLI', $instance->output);
        $this->assertStringContainsString('[[Asterios PHP Framework CLI', $instance->output);
    }

    public function testPrintTable(): void
    {
        $instance = new class($this->colorBuilder) {
            use CommandsBuilderTrait;

            public string $output = '';

            public function __construct($builder)
            {
                $this->colorBuilder = $builder;
            }

            protected function getRegisteredCommands(): array
            {
                return [
                    ['name' => 'list', 'description' => 'List all', 'group' => 'General', 'aliases' => ['ls']],
                    ['name' => 'info', 'description' => 'Show info', 'group' => 'System'],
                ];
            }

            public function run(): void
            {
                ob_start();
                $this->printTable('cli');
                $this->output = ob_get_clean();
            }
        };

        $instance->run();

        $this->assertStringContainsString('[[General:', $instance->output);
        $this->assertStringContainsString('cli list (ls)', $instance->output);
        $this->assertStringContainsString('cli info', $instance->output);
    }

    public function testPrintError(): void
    {
        $instance = new class($this->colorBuilder) {
            use CommandsBuilderTrait;

            public string $output = '';

            public function __construct($builder)
            {
                $this->colorBuilder = $builder;
            }

            public function run(): void
            {
                ob_start();
                $this->printError('Something went wrong', 'In file X');
                $this->output = ob_get_clean();
            }
        };

        $instance->run();

        $this->assertStringContainsString('[[❌ ERROR]]', $instance->output);
        $this->assertStringContainsString('[[Something went wrong]]', $instance->output);
        $this->assertStringContainsString('[[In file X]]', $instance->output);
    }

    public function testPrintDataTable(): void
    {
        $instance = new class($this->colorBuilder) {
            use CommandsBuilderTrait;

            public string $output = '';

            public function __construct($builder)
            {
                $this->colorBuilder = $builder;
            }

            public function run(): void
            {
                ob_start();
                $this->printDataTable([
                    'System Info' => [
                        'PHP Version' => '8.3',
                        'Environment' => 'production',
                        'db' => 'db',
                        'test1' => true,
                        'test2' => 'development',
                        'test3' => 'testing',
                        'test4' => 'local',
                    ],
                ]);
                $this->output = ob_get_clean();
            }
        };

        $instance->run();

        self::assertStringContainsString('[[System Info]]', $instance->output);
        self::assertStringContainsString('PHP Version', $instance->output);
        self::assertStringContainsString('db', $instance->output);
    }

    public function testPrintListTable(): void
    {
        $instance = new class($this->colorBuilder) {
            use CommandsBuilderTrait;

            public string $output = '';

            public function __construct($builder)
            {
                $this->colorBuilder = $builder;
            }

            public function run(): void
            {
                ob_start();
                $this->printListTable('Available', [
                    ['cmd' => 'start', 'desc' => 'Start system'],
                    ['cmd' => 'stop', 'desc' => 'Stop system'],
                ], 'cmd', 'desc');
                $this->output = ob_get_clean();
            }
        };

        $instance->run();

        self::assertStringContainsString('[[Available:', $instance->output);
        self::assertStringContainsString('start', $instance->output);
        self::assertStringContainsString('stop', $instance->output);
    }

    public function testGetRegisteredCommands(): void
    {
        $mockRegistry = m::mock(CommandRegistry::class);
        $mockRegistry->shouldReceive('all')
            ->once()
            ->andReturn([
                ['name' => 'test:command', 'aliases' => [], 'description' => 'A test command', 'group' => 'Test'],
            ]);

        $cli = new class {
            use CommandsBuilderTrait;

            protected function color(): object
            {
                return new class {
                    public function __call($name, $args)
                    {
                        return $this;
                    }

                    public function apply(string $text): string
                    {
                        return $text;
                    }
                };
            }
        };

        $cli->setCommandRegistry($mockRegistry);

        $commands = $cli->getRegisteredCommands();

        self::assertCount(1, $commands);
        self::assertEquals('test:command', $commands[0]['name']);
    }
}
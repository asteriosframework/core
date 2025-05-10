<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Support;

use Asterios\Core\Cli\Support\ArgumentParserTrait;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ArgumentParserTraitTest extends MockeryTestCase
{
    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    protected function makeInstance(array $args): object
    {
        return new class($args) {
            use ArgumentParserTrait;

            public function __construct(array $args)
            {
                $this->parseArguments($args);
            }

            // Expose protected methods for testing
            public function has(string $flag): bool
            {
                return $this->hasFlag($flag);
            }

            public function value(string $key): ?string
            {
                return $this->getValue($key);
            }

            public function positional(int $i): ?string
            {
                return $this->getPositional($i);
            }

            public function all(): array
            {
                return $this->allArgs();
            }
        };
    }

    public function testParseArgumentsAndAccessors(): void
    {
        $argv = ['command.php', '--env=prod', '--verbose', 'input.txt', 'other.txt'];
        $obj = $this->makeInstance($argv);

        self::assertEquals([
            '--env=prod',
            '--verbose',
            'input.txt',
            'other.txt',
        ], $obj->all());

        self::assertTrue($obj->has('--verbose'));
        self::assertFalse($obj->has('--debug'));

        self::assertEquals('prod', $obj->value('--env'));
        self::assertNull($obj->value('--missing'));

        self::assertEquals('input.txt', $obj->positional(0));
        self::assertEquals('other.txt', $obj->positional(1));
        self::assertNull($obj->positional(2));
    }
}

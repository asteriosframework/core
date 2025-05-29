<?php declare(strict_types=1);

namespace Asterios\Test\Cli;

use Asterios\Core\Cli\CommandRegistry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CommandRegistryTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testGetAllPhpFilesReturnsOnlyPhpFiles(): void
    {
        $dir = sys_get_temp_dir() . '/test_php_files_' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/file1.php', '<?php // valid');
        file_put_contents($dir . '/file2.txt', 'not php');

        $registry = new CommandRegistry();
        $result = $registry->getAllPhpFiles($dir);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertStringEndsWith('file1.php', $result[0]);

        unlink($dir . '/file1.php');
        unlink($dir . '/file2.txt');
        rmdir($dir);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAllReturnsDiscoveredCommands(): void
    {
        $mock = m::mock(CommandRegistry::class)
            ->makePartial();

        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getAllPhpFiles')
            ->andReturn([]);

        require_once __DIR__ . '/Fixtures/MockCommand.php';

        $commands = $mock->all();

        $this->assertIsArray($commands);
        $this->assertNotEmpty($commands);

        $cmd = $commands[0];
        $this->assertStringContainsString('Command', $cmd['class']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFindByNameOrAliasFindsCorrectCommand(): void
    {
        $mock = m::mock(CommandRegistry::class)
            ->makePartial();
        $mock->shouldReceive('getAllPhpFiles')
            ->andReturn([]);
        require_once __DIR__ . '/Fixtures/MockCommand.php';
        $mock->all();

        $foundByName = $mock->findByNameOrAlias('test:example');
        $foundByAlias = $mock->findByNameOrAlias('t:e');
        $notFound = $mock->findByNameOrAlias('nonexistent');

        $this->assertNotNull($foundByName);
        $this->assertSame('test:example', $foundByName['name']);

        $this->assertNotNull($foundByAlias);
        $this->assertSame('test:example', $foundByAlias['name']);

        $this->assertNull($notFound);
    }
}
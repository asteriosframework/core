<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Commands\AboutCommand;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AboutCommandTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testHandleOutputsExpectedData(): void
    {
        $expectedData = [
            'System' => [
                'PHP Version' => PHP_VERSION,
                'Framework Version' => Asterios::VERSION,
                'Environment' => Asterios::getEnvironment(),
                'Encoding' => Asterios::getEncoding(),
                'Timezone' => Asterios::getTimezone(),
            ],
        ];

        $commandMock = m::mock(AboutCommand::class)
            ->makePartial();

        $commandMock->shouldAllowMockingProtectedMethods();
        $commandMock->shouldReceive('printHeader')
            ->once()
            ->withNoArgs();

        $commandMock->shouldReceive('printDataTable')
            ->once()
            ->withArgs(function ($actualData) use ($expectedData) {

                self::assertEquals($expectedData, $actualData);

                return true;
            });

        $commandMock->handle(null);
    }
}

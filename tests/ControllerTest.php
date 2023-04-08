<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Controller;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ControllerTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @test
     * @dataProvider response_provider
     */
    public function response($content_type, $data, $expected_value): void
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['CONTENT_TYPE'] = $content_type;

        ob_start();
        (new Controller())->response($data, 204);
        $result = ob_get_clean();

        self::assertEquals($expected_value, $result);
    }

    ########## Provider ##########

    public function response_provider(): array
    {
        return [
            ['application/json', ['data' => true], '{"data":true}'],
            ['application/json', (object)['data' => true], '{"data":true}'],
            ['text/plain', 'output string', 'output string'],
        ];
    }
}

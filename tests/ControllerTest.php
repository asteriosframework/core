<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Controller;
use Asterios\Core\Http\ContentType;
use Asterios\Core\Http\Disposition;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @runTestsInSeparateProcesses
 */
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

    /**
     * @test
     */
    public function set_content_type_is_fluent(): void
    {
        $controller = new Controller();

        self::assertSame(
            $controller,
            $controller->setContentType(ContentType::JSON)
        );
    }

    /**
     * @test
     */
    public function set_content_disposition_is_fluent(): void
    {
        $controller = new Controller();

        self::assertSame(
            $controller,
            $controller->setContentDisposition(
                Disposition::INLINE,
                'calendar.ics'
            )
        );
    }

    /**
     * @test
     */
    public function inline_is_fluent(): void
    {
        $controller = new Controller();

        self::assertSame(
            $controller,
            $controller->inline('calendar.ics')
        );
    }

    /**
     * @test
     */
    public function attachment_is_fluent(): void
    {
        $controller = new Controller();

        self::assertSame(
            $controller,
            $controller->attachment('invoice.pdf')
        );
    }


    ########## Provider ##########

    public static function response_provider(): array
    {
        return [
            [ContentType::JSON, ['data' => true], '{"data":true}'],
            [ContentType::JSON, (object)['data' => true], '{"data":true}'],
            [ContentType::PLAIN, 'output string', 'output string'],
        ];
    }
}

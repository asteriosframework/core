<?php declare(strict_types=1);

namespace Asterios\Core
{
    /**
     * Summary of Asterios\Core\debug_backtrace
     * @param int $options
     * @param int $limit
     * @return array<int,array<string,string|int>>
     */
    function debug_backtrace(int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
    {
        return [
            [
                'file' => 'core/debug.php',
                'line' => 123,
            ],
            [
                'file' => 'strpos_false',
                'line' => 321,
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @return int|false
     */
    function strpos(string $haystack, string $needle, int $offset = 0)
    {
        return ($haystack !== 'strpos_false') ? 1 : false;
    }
}

namespace Asterios\Test
{

    use Asterios\Core\Debug;
    use Mockery as m;
    use Mockery\Adapter\Phpunit\MockeryTestCase;

    /**
     * @runTestsInSeparateProcesses
     */
    class DebugTest extends MockeryTestCase
    {
        protected function tearDown(): void
        {
            m::close();
        }

        /**
         * @test
         */
        public function dump(): void
        {
            ob_start();
            Debug::dump();
            $result = ob_get_clean();

            self::assertStringContainsString('strpos_false', $result);
        }

    }
}
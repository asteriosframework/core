<?php declare(strict_types=1);

namespace Asterios\Core
{
    function is_readable(string $filename): bool
    {
        if (strpos($filename, 'unreadable'))
        {
            return false;
        }

        return true;
    }

    function fopen(string $filename)
    {
        if (strpos($filename, 'fopenFailed'))
        {
            return false;
        }

        return \fopen($filename, 'rb');
    }
}

namespace Asterios\Test
{

    use Asterios\Core\Env;
    use Asterios\Core\Exception\EnvException;
    use Asterios\Core\Exception\EnvItemNotFoundException;
    use Asterios\Core\Exception\EnvLoadException;
    use Mockery as m;
    use Mockery\Adapter\Phpunit\MockeryTestCase;

    class EnvTest extends MockeryTestCase
    {
        protected Env $testedClass;

        protected function setUp(): void
        {
            parent::setUp();
            $this->testedClass = (new Env('tests/testdata/.env'));
        }

        protected function tearDown(): void
        {
            m::close();
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function loadEnvWithEmptyNameException(): void
        {
            $this->expectException(EnvException::class);

            (new Env(''));
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function loadEnvException(): void
        {
            $this->expectException(EnvLoadException::class);

            (new Env('tests/testdata/.env.example'));
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function unreadableEnvFileException(): void
        {
            $this->expectException(EnvLoadException::class);

            (new Env('tests/testdata/.env.unreadable'));
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function fopenFailedOnEnvFileException(): void
        {
            $this->expectException(EnvLoadException::class);

            (new Env('tests/testdata/.env.fopenFailed'));
        }

        /**
         * @test
         * @runInSeparateProcess
         * @dataProvider getProvider
         */
        public function get(string $item, string|int|bool|array|null $default, string|int|bool|array|null $expected): void
        {
            $actual = $this->testedClass->get($item, $default);

            self::assertEquals($expected, $actual);
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function getRequiredException(): void
        {
            $this->expectException(EnvItemNotFoundException::class);

            $this->testedClass->getRequired('REQUIRED_ITEM');
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function getRequired(): void
        {

            $actual = $this->testedClass->getRequired('SECURITY_INPUT_FILTER');

            self::assertEquals('xss_clean', $actual);

        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function getArrayWithDefaultArray(): void
        {
            $actual = $this->testedClass->getArray('ARRAY_NOT_IN_ENV', ['default' => 'value']);

            self::assertEquals(['default' => 'value'], $actual);
        }

        /**
         * @test
         * @runInSeparateProcess
         * @dataProvider getArrayProvider
         */
        public function getArray(string $item, array $expected): void
        {
            $actual = $this->testedClass->getArray($item);

            self::assertEquals($expected, $actual);
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function getArrayPrefixedWithoutPrefixException(): void
        {
            $this->expectException(EnvException::class);

            $this->testedClass->getArrayPrefixed('');
        }

        /**
         * @test
         * @runInSeparateProcess
         */
        public function getArrayFromPrefixed(): void
        {
            $actual = $this->testedClass->getArrayPrefixed('PREFIXED_ARRAY_');

            $expected = [
                'USER1' => 'john',
                'USER2' => 'jim',
                'USER3' => 'joe',
            ];

            self::assertEquals($expected, $actual);
        }

        // ###

        public static function getProvider(): array
        {
            return [
                ['ITEM_NOT_IN_ENV', null, null],
                ['EXAMPLE_IS_EMPTY', null, null],
                ['EXAMPLE_ONLY_SPACES', null, ''],
                ['EXAMPLE_WITH_WHITESPACES', null, 'foo bar baz'],
                ['EXAMPLE_TRUE', null, true],
                ['EXAMPLE_FALSE', null, false],
                ['EXAMPLE_NULL', null, null],
                ['EXAMPLE_LOWERCASE', null, 'abcdef'],
                ['EXAMPLE_UPPERCASE', null, 'ABCDEF'],
            ];
        }

        public static function getArrayProvider(): array
        {
            return [
                ['EXAMPLE_ARRAY1', ['foo']],
                ['EXAMPLE_ARRAY2', ['foo', 'bar']],
            ];
        }
    }
}
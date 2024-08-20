<?php declare(strict_types=1);

namespace Asterios\Test
{

    use Asterios\Core\Env;
    use Asterios\Core\Exception\EnvException;
    use Asterios\Core\Exception\EnvItemNotFoundException;
    use Asterios\Core\Exception\EnvLoadException;
    use Asterios\Core\File;
    use Mockery as m;
    use Mockery\Adapter\Phpunit\MockeryTestCase;

    class EnvTest extends MockeryTestCase
    {
        protected Env $testedClass;

        protected function setUp(): void
        {
            parent::setUp();
            $this->testedClass = (new Env('tests/testdata/.env.test'));
        }

        protected function tearDown(): void
        {
            m::close();
        }

        /**
         * @test
         */
        public function loadEnvWithEmptyNameException(): void
        {
            $this->expectException(EnvException::class);

            (new Env(''))->get('EXAMPLE');
        }

        /**
         * @test
         */
        public function EnvFileIsNoFileException(): void
        {
            $envFile = 'tests/testdata/.env.example';

            $fileMock = m::mock(File::class);
            $fileMock->shouldReceive('is_file')
                ->with($envFile)
                ->andReturnFalse();

            $this->expectException(EnvLoadException::class);

            (new Env($envFile))->setFile($fileMock)
                ->get('EXAMPLE');
        }

        /**
         * @test
         */
        public function EnvFileNotReadableException(): void
        {
            $envFile = 'tests/testdata/.env.unreadable';

            $fileMock = m::mock(File::class);
            $fileMock->shouldReceive('is_file')
                ->with($envFile)
                ->andReturnTrue();
            $fileMock->shouldReceive('isReadable')
                ->with($envFile)
                ->andReturnFalse();

            $this->expectException(EnvLoadException::class);

            (new Env($envFile))->setFile($fileMock)
                ->get('EXAMPLE');
        }

        /**
         * @test
         */
        public function fopenFailedOnEnvFileException(): void
        {
            $envFile = 'tests/testdata/.env.fopenFailed';

            $fileMock = m::mock(File::class);
            $fileMock->shouldReceive('is_file')
                ->with($envFile)
                ->andReturnTrue();
            $fileMock->shouldReceive('isReadable')
                ->andReturnTrue();
            $fileMock->shouldReceive('open')
                ->andReturnFalse();

            $this->expectException(EnvLoadException::class);

            (new Env($envFile))->setFile($fileMock)
                ->get('EXAMPLE');
        }

        /**
         * @test
         * @dataProvider getProvider
         */
        public function get(string $item, string|int|bool|array|null $default, string|int|bool|array|null $expected): void
        {
            $actual = $this->testedClass->get($item, $default);

            self::assertEquals($expected, $actual);
        }

        /**
         * @test
         */
        public function getRequiredException(): void
        {
            $this->expectException(EnvItemNotFoundException::class);

            $this->testedClass->getRequired('REQUIRED_ITEM');
        }

        /**
         * @test
         */
        public function getRequired(): void
        {

            $actual = $this->testedClass->getRequired('SECURITY_INPUT_FILTER');

            self::assertEquals('xss_clean', $actual);

        }

        /**
         * @test
         */
        public function getArrayWithDefaultArray(): void
        {
            $actual = $this->testedClass->getArray('ARRAY_NOT_IN_ENV', ['default' => 'value']);

            self::assertEquals(['default' => 'value'], $actual);
        }

        /**
         * @test
         * @dataProvider getArrayProvider
         */
        public function getArray(string $item, array $expected): void
        {
            $actual = $this->testedClass->getArray($item);

            self::assertEquals($expected, $actual);
        }

        /**
         * @test
         */
        public function getArrayPrefixedWithoutPrefixException(): void
        {
            $this->expectException(EnvException::class);

            $this->testedClass->getArrayPrefixed('');
        }

        /**
         * @test
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
                ['EXAMPLE_WITH_UPPER_AND_LOWERCASE_VALUE', null, 'JiM'],
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
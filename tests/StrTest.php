<?php

namespace Asterios\Test;

use Asterios\Core\Exception\StrRandomBytesException;
use Asterios\Core\Str;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class StrTest extends TestCase
{
    /**
     * @test
     * @dataProvider startsWithProvider
     * @param string $string
     * @param string $startWith
     * @param bool $expected
     */
    public function startsWith(string $string, string $startWith, bool $expected): void
    {
        $actual = Str::getInstance()
            ->startsWith($string, $startWith);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider endsWithProvider
     * @param string $string
     * @param string $startWith
     * @param bool $expected
     */
    public function endsWith(string $string, string $startWith, bool $expected): void
    {
        $actual = Str::getInstance()
            ->endsWith($string, $startWith);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider subProvider
     * @param string $string
     * @param int $start
     * @param int $length
     * @param string $encoding
     * @param string $expected
     */
    public function sub(string $string, int $start, int $length, string $encoding, string $expected): void
    {
        $actual = Str::getInstance()
            ->sub($string, $start, $length, $encoding);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider lengthProvider
     * @param string $string
     * @param string $encoding
     * @param int $expectedValue
     */
    public function length(string $string, string $encoding, int $expectedValue): void
    {
        $actual = Str::getInstance()
            ->length($string, $encoding);

        self::assertEquals($expectedValue, $actual);
    }

    /**
     * @test
     * @dataProvider lowerProvider
     * @param string $string
     * @param string $encoding
     * @param string $expected
     */
    public function lower(string $string, string $encoding, string $expected): void
    {
        $actual = Str::getInstance()
            ->lower($string, $encoding);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider upperProvider
     * @param string $string
     * @param string $encoding
     * @param string $expected
     */
    public function upper(string $string, string $encoding, string $expected): void
    {
        $actual = Str::getInstance()
            ->upper($string, $encoding);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider isJsonProvider
     * @param string $string
     * @param bool $expected
     */
    public function isJsonTest(string $string, bool $expected): void
    {
        $actual = Str::getInstance()
            ->isJson($string);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider isXmlProvider
     * @param string $string
     * @param bool $expected
     * @throws \Exception
     */
    public function isXml(string $string, bool $expected): void
    {
        $actual = Str::getInstance()
            ->isXml($string);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @param string $string
     * @dataProvider isSerializedProvider
     * @param bool $expected
     */
    public function isSerialized(string $string, bool $expected): void
    {
        $actual = Str::getInstance()
            ->isSerialized($string);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @param string $string
     * @dataProvider isHtmlProvider
     * @param bool $expected
     */
    public function isHtml(string $string, bool $expected): void
    {
        $actual = Str::getInstance()
            ->isHtml($string);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider filterKeysProvider
     * @param array $array
     * @param array $keys
     * @param bool $remove
     * @param array $expected
     */
    public function filterKeys(array $array, array $keys, bool $remove, array $expected): void
    {
        $actual = Str::getInstance()
            ->filterKeys($array, $keys, $remove);
        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomBasic(): void
    {
        $actual = Str::getInstance()
            ->random('basic', 0);
        /** @var string $max_length */
        $max_length = mt_getrandmax();
        self::assertTrue($actual !== '' && strlen($actual) <= strlen($max_length));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomAlnum(): void
    {
        $actual = Str::getInstance()
            ->random('alnum', 10);

        self::assertSame(strlen($actual), 10);
        self::assertMatchesRegularExpression('/[a-zA-Z0-9]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomNumeric(): void
    {
        $actual = Str::getInstance()
            ->random('numeric', 15);

        self::assertSame(strlen($actual), 15);
        self::assertMatchesRegularExpression('/[0-9]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomNozero(): void
    {
        $actual = Str::getInstance()
            ->random('nozero', 10);

        self::assertSame(strlen($actual), 10);
        self::assertMatchesRegularExpression('/[1-9]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomAlpha(): void
    {
        $actual = Str::getInstance()
            ->random('alpha', 10);

        self::assertSame(strlen($actual), 10);
        self::assertMatchesRegularExpression('/[a-zA-Z]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomDistinct(): void
    {
        $actual = Str::getInstance()
            ->random('distinct', 20);

        self::assertSame(strlen($actual), 20);
        self::assertMatchesRegularExpression('/[2-9ACDEFHJKLMNPRSTUVWXYZ]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomHexdec(): void
    {
        $actual = Str::getInstance()
            ->random('distinct', 20);

        self::assertSame(strlen($actual), 20);
        self::assertMatchesRegularExpression('/[0-9abcdef]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomUnique(): void
    {
        $actual = Str::getInstance()
            ->random('unique', 0);

        self::assertSame(strlen($actual), 32);
        self::assertMatchesRegularExpression('/[0-9a-zA-Z]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomSha1(): void
    {
        $actual = Str::getInstance()
            ->random('sha1', 0);

        self::assertSame(strlen($actual), 40);
        self::assertMatchesRegularExpression('/[0-9a-zA-Z]/', $actual);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function randomUuid(): void
    {
        $actual = Str::getInstance()
            ->random('uuid', 0);
        self::assertSame(strlen($actual), 36);
        self::assertMatchesRegularExpression('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $actual);
    }

    /**
     * @test
     */
    public function getInstanceUniqueness(): void
    {
        $firstCall = Str::getInstance();
        $secondCall = Str::getInstance();

        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf(Str::class, $firstCall);
        self::assertSame($firstCall, $secondCall);
    }

    /**
     * @test
     * @dataProvider trimDataProvider
     */
    public function trim($value, $expected, $characters): void
    {
        if ($characters === null)
        {
            $actual = Str::getInstance()
                ->trim($value);
        }
        else
        {
            $actual = Str::getInstance()
                ->trim($value, $characters);
        }

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws StrRandomBytesException
     */
    public function testGenerateBase32SecretReturnsValidString(): void
    {

        $length = 16;
        $secret = Str::getInstance()->generateBase32Secret($length);

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);

        $expectedLength = (int) ceil(($length * 8) / 5);
        $this->assertEquals($expectedLength, strlen($secret));
    }

    /**
     * @throws StrRandomBytesException
     */
    public function testGenerateBase32SecretWithDifferentLength(): void
    {

        $length = 10;
        $secret = Str::getInstance()->generateBase32Secret($length);

        $expectedLength = (int) ceil(($length * 8) / 5);
        $this->assertEquals($expectedLength, strlen($secret));
    }

    ########## Provider ##########

    public static function startsWithProvider(): array
    {
        return [
            ['foo_bar', 'foo_', true],
            ['foo_bar', 'bar', false],
        ];
    }

    public static function endsWithProvider(): array
    {
        return [
            ['foo_bar', '_bar', true],
            ['foo_bar', '_foo', false],
        ];
    }

    public static function subProvider(): array
    {
        return [
            ['foo', 1, 2, 'ISO-8859-15', 'oo'],
            ['Über', 0, 1, 'UTF-8', 'Ü'],
            ['€', 0, 1, 'UTF-8', '€'],
        ];
    }

    public static function lengthProvider(): array
    {
        return [
            ['foo', 'UTF-8', 3],
            ['foo', 'ISO-8859-15', 3],
        ];
    }

    public static function lowerProvider(): array
    {
        return [
            ['FOO', 'UTF-8', 'foo'],
            ['BAR', 'ISO-8859-15', 'bar'],
            ['ÄÖÜ', 'UTF-8', 'äöü'],
        ];
    }

    public static function upperProvider(): array
    {
        return [
            ['foo', 'UTF-8', 'FOO'],
            ['bar', 'ISO-8859-15', 'BAR'],
            ['äöü', 'UTF-8', 'ÄÖÜ'],
        ];
    }

    public static function isJsonProvider(): array
    {
        return [
            ['{"foo": "bar"}', true],
            ['foo', false],
            ['<xml><foo></foo></xml>', false],
        ];
    }

    public static function isXmlProvider(): array
    {
        return [
            ['<xml><foo></foo></xml>', true],
            ['foo', false],
            ['{"foo": "bar"}', false],
        ];
    }

    public static function isSerializedProvider(): array
    {
        return [
            [serialize('foo'), true],
            ['foo', false],
        ];
    }

    public static function isHtmlProvider(): array
    {
        return [
            ['<html lang="en"><head><title>Test</title></head><body></body></html>', true],
            ['foo', false],
        ];
    }

    public static function filterKeysProvider(): array
    {
        return [
            [['foo' => 'bar', 'bar' => 'foo'], ['foo'], false, ['foo' => 'bar']],
            [['foo' => 'bar', 'bar' => 'foo'], ['foo'], true, ['bar' => 'foo']],
        ];
    }

    public static function trimDataProvider(): array
    {
        return [
            [0, 0, null],
            ["  Foo  bar    \n", "Foo  bar", null],
            ["|Foo  Bar |", "Foo  Bar ", "|"],
        ];
    }
}

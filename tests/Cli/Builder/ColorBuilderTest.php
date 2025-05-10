<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Builder;

use Asterios\Core\Cli\Builder\ColorBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ColorBuilderTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testGreenText(): void
    {
        $text = 'Hello';
        $expected = "\033[92m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::greenText($text));
    }

    public function testBoldGreenText(): void
    {
        $text = 'Success';
        $expected = "\033[1;92m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::boldGreenText($text));
    }

    public function testRedText(): void
    {
        $text = 'Error';
        $expected = "\033[91m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::redText($text));
    }

    public function testBoldRedText(): void
    {
        $text = 'Critical';
        $expected = "\033[1;91m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::boldRedText($text));
    }

    public function testYellowText(): void
    {
        $text = 'Warning';
        $expected = "\033[93m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::yellowText($text));
    }

    public function testBoldYellowText(): void
    {
        $text = 'Warning';
        $expected = "\033[1;93m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::boldYellowText($text));
    }

    public function testCyanText(): void
    {
        $text = 'My cyan text';
        $expected = "\033[96m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::cyanText($text));
    }

    public function testBoldCyanText(): void
    {
        $text = 'My cyan bold text';
        $expected = "\033[1;96m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::boldCyanText($text));
    }

    public function testMagentaText(): void
    {
        $text = 'My magenta text';
        $expected = "\033[95m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::magentaText($text));
    }

    public function testBoldMagentaText(): void
    {
        $text = 'My magenta bold text';
        $expected = "\033[1;95m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::boldMagentaText($text));
    }

    public function testBlackText(): void
    {
        $text = 'My black text';
        $expected = "\033[90m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::blackText($text));
    }

    public function testBoldBlackText(): void
    {
        $text = 'My black bold text';
        $expected = "\033[1;90m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::boldBlackText($text));
    }

    public function testBoldText(): void
    {
        $text = 'Bold';
        $expected = "\033[1m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::boldText($text));
    }

    public function testMultipleCodes(): void
    {
        $builder = ColorBuilder::create()
            ->bold()
            ->cyan()
            ->white();
        $result = $builder->apply('Test');
        $expected = "\033[1;96;97mTest\033[0m";

        self::assertEquals($expected, $result);
    }

    public function testSuccessTextAlias(): void
    {
        $text = 'OK';
        $expected = ColorBuilder::boldGreenText($text);

        self::assertEquals($expected, ColorBuilder::successText($text));
    }

    public function testErrorTextAlias(): void
    {
        $text = 'Fail';
        $expected = ColorBuilder::boldRedText($text);

        self::assertEquals($expected, ColorBuilder::errorText($text));
    }

    public function testGrayText(): void
    {
        $text = 'Info';
        $expected = "\033[33m{$text}\033[0m";

        self::assertEquals($expected, ColorBuilder::grayText($text));
    }
}
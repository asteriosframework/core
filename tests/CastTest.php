<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Cast;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CastTest extends MockeryTestCase
{
    protected Cast $testedClass;

    public function testInt(): void
    {
        $actual = Cast::forge()
            ->int('1');

        self::assertIsInt($actual);
    }

    public function testIsString(): void
    {
        $actual = Cast::forge()
            ->string(1);

        self::assertIsString($actual);
    }

    public function testBool(): void
    {
        $actual = Cast::forge()
            ->bool('false');

        self::assertIsBool($actual);
    }

    public function testIsDouble(): void
    {
        $actual = Cast::forge()
            ->double('1.1');

        self::assertIsFloat($actual);
    }

    public function testIsFloat(): void
    {
        $actual = Cast::forge()
            ->float('1.1111');

        self::assertIsFloat($actual);
    }

    public function testIsObject(): void
    {
        $actual = Cast::forge()
            ->object([0 => ['name' => 'John Doe']]);

        self::assertIsObject($actual);
    }

    public function testIsStringToArray(): void
    {
        $actual = Cast::forge()->stringToArray('one,two,three');

        self::assertEquals(['one', 'two', 'three'], $actual);
    }

    public function testIsArrayToString(): void
    {
        $actual = Cast::forge()->arrayToString(['one','two','three']);
        $expected = 'one,two,three';

        self::assertEquals($expected, $actual);
    }
}

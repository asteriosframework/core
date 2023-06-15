<?php

namespace Asterios\Test;

use Asterios\Core\Math;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    protected Math $testedClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testedClass = new Math;
        $this->testedClass->setTax(19);
        $this->testedClass->setCurrency('EUR');
    }

    public function test_netto(): void
    {
        $actual = $this->testedClass->netto(19.95);

        self::assertEquals(16.76, $actual);
    }

    public function test_brutto(): void
    {
        $actual = $this->testedClass->brutto(16.764);

        self::assertEquals(19.95, $actual);
    }

    public function test_percentageValue(): void
    {
        $actual = $this->testedClass->percentageValue(16.764, 19);

        self::assertEquals(3.19, $actual);
    }

    public function test_percentage(): void
    {
        $actual = $this->testedClass->percentage(3.19, 16.764, 0);

        self::assertEquals(19, $actual);
    }
}

<?php declare(strict_types=1);

namespace Asterios\Test\Orm;

use Asterios\Core\Orm\OrmSqlFormatter;
use PHPUnit\Framework\TestCase;

class OrmSqlFormatterTest extends TestCase
{
    private OrmSqlFormatter $formatter;

    public function testBackticksSimpleColumn(): void
    {
        $this->assertSame('`name`', $this->formatter->backticks('name'));
    }

    public function testBackticksWithAlias(): void
    {
        $this->assertSame('`users`.`id`', $this->formatter->backticks('users.id'));
    }

    public function testBackticksWithAsAlias(): void
    {
        $this->assertSame('`name` AS username', $this->formatter->backticks('name AS username'));
    }

    public function testFormatValueNumeric(): void
    {
        $this->assertSame('10', $this->formatter->formatValue(10));
        $this->assertSame('10.5', $this->formatter->formatValue(10.5));
    }

    public function testFormatValueString(): void
    {
        $this->assertSame('"test"', $this->formatter->formatValue('test'));
    }

    public function testFormatInOperator(): void
    {
        $this->assertSame('(1,2,3)', $this->formatter->formatInOperator('1,2,3'));
    }

    public function testIsOperatorNull(): void
    {
        $this->assertTrue($this->formatter->isOperatorNull('IS NULL'));
        $this->assertTrue($this->formatter->isOperatorNull('IS NOT NULL'));
        $this->assertFalse($this->formatter->isOperatorNull('='));
    }

    public function testBackticksWithImplicitAlias(): void
    {
        $formatter = new OrmSqlFormatter();

        $this->assertSame(
            '`name` AS username',
            $formatter->backticks('name username')
        );
    }

    public function testBackticksWithMd5(): void
    {
        $formatter = new OrmSqlFormatter();

        $this->assertSame(
            'MD5(`email`)',
            $formatter->backticks('MD5(email)')
        );
    }

    public function testBackticksWithMd5AlreadyBackticked(): void
    {
        $formatter = new OrmSqlFormatter();

        $this->assertSame(
            'MD5(`email`)',
            $formatter->backticks('MD5(`email`)')
        );
    }

    public function testOpen(): void
    {
        $formatter = new OrmSqlFormatter();
        $this->assertSame('(', $formatter->open());
    }

    public function testClose(): void
    {
        $formatter = new OrmSqlFormatter();
        $this->assertSame(')', $formatter->close());
    }
    
    protected function setUp(): void
    {
        $this->formatter = new OrmSqlFormatter();
    }
}

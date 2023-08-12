<?php

declare(strict_types=1);

namespace Asterios\Test\Db\ORM\Support;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Asterios\Core\Db\ORM\Support\DatatypeMapper;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\TestDox;

#[CoversClass(DatatypeMapper::class)]
class DatatypeMapperTest extends MockeryTestCase
{
    #[TestDox('Test call dbTypeVarchar() successful')]
    #[CoversFunction('DataTypeMapper::__callStatic')]
    public function test_dbTypeVarchar_success(): void
    {
        $actual = DatatypeMapper::dbTypeVarchar();

        self::assertEquals('string', $actual);
    }

    #[TestDox('Test call dbTypeUknown() throws exception')]
    #[CoversFunction('DataTypeMapper::__callStatic')]
    #[CoversFunction('DataTypeMapper::badMethod')]
    public function test_dbType_exception(): void
    {
        self::expectException(\BadMethodCallException::class);

        $actual = DatatypeMapper::dbTypeUnknown();
    }

    #[TestDox('Test call castVarchar() successful')]
    #[CoversFunction('DataTypeMapper::__callStatic')]
    public function test_castVarchar_success(): void
    {
        $actual = DatatypeMapper::castVarchar('xyz');

        self::assertEquals('xyz', $actual);
        self::assertIsString($actual);
    }

    #[TestDox('Test call castUnknown() throws exception')]
    #[CoversFunction('DataTypeMapper::__callStatic')]
    #[CoversFunction('DataTypeMapper::badMethod')]
    public function test_cast_exception(): void
    {
        self::expectException(\BadMethodCallException::class);

        $actual = DatatypeMapper::castUnknown('xyz');
    }

    #[TestDox('Test call castDate() successful')]
    #[CoversFunction('DataTypeMapper::__callStatic')]
    #[CoversFunction('DataTypeMapper::phpCast')]
    public function test_cast_date_success(): void
    {
        $actual = DatatypeMapper::castDate('2023-02-12');

        self::assertInstanceOf(\DateTime::class, $actual);
        self::assertEquals('12.02.2023', $actual->format('d.m.Y'));
    }

    #[TestDox('Test call castDate() throws exception')]
    #[CoversFunction('DataTypeMapper::__callStatic')]
    #[CoversFunction('DataTypeMapper::phpCast')]
    #[CoversFunction('DataTypeMapper::badMethod')]
    public function test_cast_date_exception(): void
    {
        self::expectException(\BadMethodCallException::class);

        $actual = DatatypeMapper::castDate('2023-02-52');
    }

    #[TestDox('Test call whith unknow method prefix')]
    #[CoversFunction('DataTypeMapper::__callStatic')]
    #[CoversFunction('DataTypeMapper::badMethod')]
    public function test_unknown_method_prefix(): void
    {
        self::expectException(\BadMethodCallException::class);

        $actual = DatatypeMapper::whereNotExists();
    }

}
<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Collection;
use Asterios\Core\Exception\CollectionException;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CollectionTest extends MockeryTestCase
{
    protected Collection $testedClass;

    public function testAll(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->all();

        self::assertEquals($data, $actual);
    }

    public function testAdd(): void
    {
        $data = [];

        $expectedData = array_merge($data, ['myTest']);

        $this->testedClass = Collection::forge($data);
        $this->testedClass->add('myTest');

        $actual = $this->testedClass->all();

        self::assertEquals($expectedData, $actual);
    }

    public function testMap(): void
    {
        $data = [1];

        $this->testedClass = Collection::forge($data);

        $this->testedClass->map(function ($item) {
            return $item * 2;
        });

        $actual = $this->testedClass->offsetGet(0);

        self::assertEquals(2, $actual);
    }

    public function testFilter(): void
    {
        $data = [1 => 'John', 2 => 'Jim', 3 => 'Joe', 4 => 'Jolie'];

        $this->testedClass = Collection::forge($data);

        $this->testedClass->filter(function ($item) {
            return $item === 'Jolie';
        });

        $filtered = $this->testedClass->all();

        self::assertEquals([4 => 'Jolie'], $filtered);
    }

    public function testReduce(): void
    {

        $this->testedClass = Collection::forge([1, 2, 3, 4, 5]);

        $actual = $this->testedClass->reduce(function ($carry, $item) {
            return $carry + $item;
        }, 0);

        self::assertEquals(15, $actual);
    }

    public function testFirst(): void
    {
        $data = [12345, 67890];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->first();

        self::assertEquals(12345, $actual);
    }

    public function testFirstWithDefault(): void
    {
        $data = ['Jim'];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->first(function ($item) {
            return $item === 'John';
        }, 98765);

        self::assertEquals(98765, $actual);
    }

    public function testOffsetExists(): void
    {
        $data = [12345 => ['username' => 'john'], 67890 => ['username' => 'jim']];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->offsetExists(67890);

        self::assertTrue($actual);
    }

    public function testoffsetSetWithoutOffset(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $expectedData = array_merge($data, [4]);

        $this->testedClass = Collection::forge($data);
        $this->testedClass->offsetSet(null, 4);

        $actual = $this->testedClass->all();

        self::assertEquals($expectedData, $actual);
    }

    public function testoffsetSetWithOffset(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];

        $expectedData = array_merge($data, ['d' => 4]);

        $this->testedClass = Collection::forge($data);
        $this->testedClass->offsetSet('d', 4);

        $actual = $this->testedClass->all();

        self::assertEquals($expectedData, $actual);
    }

    public function testoffsetUnset(): void
    {
        $data = [12345 => ['username' => 'john'], 67890 => ['username' => 'jim']];

        $this->testedClass = Collection::forge($data);

        $this->testedClass->offsetUnset(67890);

        $expectedData = [12345 => ['username' => 'john']];

        $actual = $this->testedClass->all();

        self::assertEquals($expectedData, $actual);
    }

    public function testCount(): void
    {
        $data = ['a', 'b', 'c'];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->count();

        self::assertEquals(3, $actual);
    }

    public function testGetIterator(): void
    {
        $data = [12345 => ['username' => 'john'], 67890 => ['username' => 'jim']];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->getIterator();

        self::assertInstanceOf(\ArrayIterator::class, $actual);
    }

    public function testToJson(): void
    {
        $data = [12345 => ['username' => 'john'], 67890 => ['username' => 'jim']];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->toJson();

        self::assertEquals(json_encode($data, JSON_THROW_ON_ERROR), $actual);
    }

    public function testToJsonWithException(): void
    {
        $this->expectException(CollectionException::class);

        $resource = fopen('php://temp', 'rb');
        $this->testedClass = Collection::forge(['resource' => $resource]);

        $this->testedClass->toJson();

        fclose($resource);
    }

    public function testToArray(): void
    {
        $data = [12345, 67890];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->toArray();

        self::assertIsArray($actual);
    }

    public function testToObject(): void
    {
        $data = [12345, 67890];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->toObject();

        self::assertIsObject($actual);
    }

    public function testIsEmpty(): void
    {
        $data = [];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->isEmpty();

        self::assertTrue($actual);
    }

    public function testFlip(): void
    {
        $data = [1 => 'John', 2 => 'Jim'];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->flip();

        self::assertEquals(['John' => 1, 'Jim' => 2], $actual);
    }

    public function testSum(): void
    {
        $data = [10, 50, 40];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->sum();

        self::assertEquals(100, $actual);
    }

    public function testReverse(): void
    {
        $data = [1 => 'John', 2 => 'Jim'];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->reverse();

        self::assertEquals([0 => 'Jim', 1 => 'John'], $actual);
    }

    public function testReverseWithPreserveKeys(): void
    {
        $data = [0 => 'John', 1 => 'Jim'];

        $this->testedClass = Collection::forge($data, true);

        $actual = $this->testedClass->reverse();

        self::assertEquals([1 => 'John', 0 => 'Jim'], $actual);
    }

    public function testAvgAsFloat(): void
    {
        $data = [3, 4, 2, 2, 1, 4];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->avg();

        self::assertEquals(2.6666666666666665, $actual);
    }

    public function testAvgWithoutDecimal(): void
    {
        $data = [3, 4, 2, 2, 1, 4];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->avg(true);

        self::assertEquals(2, $actual);
    }

    public function testHasItemsSuccess(): void
    {
        $data = [0 => 'a'];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->hasItems();

        self::assertTrue($actual);
    }

    public function testHasItemsFalse(): void
    {
        $data = [];

        $this->testedClass = Collection::forge($data);

        $actual = $this->testedClass->hasItems();

        self::assertFalse($actual);
    }
}
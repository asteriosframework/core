<?php

declare(strict_types=1);

namespace Asterios\Test\Db\ORM\Support\Collections;

use Asterios\Core\Db\ORM\Support\Collections\ResultCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\TestDox;

#[CoversClass(ResultCollection::class)]
class ResultCollectionTest extends MockeryTestCase
{
    #[TestDox('Create Collection with constructor')]
    #[CoversFunction('ResultCollection::__contruct')]
    public function test_create_collection_over_constructor_objects(): void
    {
        $expected = [
            (object) [
                'name' => 'Jimi Hendrix'
            ],
            (object) [
                'name' => 'Eric Clapton'
            ],
        ];

        $actual = new ResultCollection([
            (object) [
                'name' => 'Jimi Hendrix'
            ],
            (object) [
                'name' => 'Eric Clapton'
            ],
        ]);

        self::assertEquals($expected, $actual->getArrayCopy());
        self::assertEquals($expected[1]->name, $actual->offsetGet(1)->name);
    }

    public function test_add_item_to_collection(): void
    {
        $item = (object) [
            'name' => 'Janis Joplin',
        ];

        $expected = [
            $item,
        ];

        $actual = new ResultCollection();

        $actual->append($item);

        self::assertEquals($expected, $actual->getArrayCopy());
        self::assertEquals($item->name, $actual->offsetGet(0)->name);
    }

    public function test_collection_add_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        $actual = new ResultCollection();
        $actual->append('Invalid value');
    }
}
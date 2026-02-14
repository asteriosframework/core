<?php declare(strict_types=1);

namespace Asterios\Core\Orm;

final readonly class OrmMetadata
{
    public function __construct(
        public string $table,
        public ?string $alias,
        public string $connection
    )
    {
    }
}
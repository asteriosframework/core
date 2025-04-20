<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
    public function __construct(
        public string $name,
        public string $description = '',
        public string $group = 'Allgemein',
        public array $aliases = []
    )
    {
    }
}
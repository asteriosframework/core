<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
    public string $name;
    public string $description;
    public string $group;
    public array $aliases;
    public array $options;

    public function __construct(
        string $name,
        string $description = '',
        string $group = 'General',
        array $aliases = [],
        array $options = []
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->group = $group;
        $this->aliases = $aliases;
        $this->options = $options;
    }
}

<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface ColumnDefinitionBuilderInterface
{
    /**
     * @return self
     */
    public function nullable(): self;

    /**
     * @return self
     */
    public function notNull(): self;

    /**
     * @param string|int|null $value
     * @return self
     */
    public function default(string|int|null $value): self;

    /**
     * @return self
     */
    public function unique(): self;

    /**
     * @return void
     */
    public function build(): void;
}

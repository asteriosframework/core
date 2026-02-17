<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Asterios\Core\Exception\ConfigLoadException;

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
     * @param bool $isExpression
     * @return self
     */
    public function default(string|int|null $value, bool $isExpression = false): self;

    /**
     * @return self
     */
    public function unique(): self;

    /**
     * @return void
     * @throws ConfigLoadException
     */
    public function build(): void;
}

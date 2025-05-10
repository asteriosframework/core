<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface IndexBuilderInterface
{
    /**
     * @return self
     */
    public function unique(): self;

    /**
     * @return void
     */
    public function add(): void;
}
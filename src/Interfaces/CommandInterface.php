<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

interface CommandInterface
{
    /**
     * @param string|null $argument
     * @return void
     */
    public function handle(?string $argument): void;

}
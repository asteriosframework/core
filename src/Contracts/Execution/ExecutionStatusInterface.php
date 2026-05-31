<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Execution;

interface ExecutionStatusInterface
{
    /**
     * @param string $identifier
     * @return bool
     */
    public function hasRun(string $identifier): bool;

    /**
     * @param string $identifier
     * @return void
     */
    public function markAsRun(string $identifier): void;
}
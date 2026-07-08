<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Session\Store;

interface SessionStoreInterface
{
    /**
     * @return void
     */
    public function start(): void;

    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * @return array<string, mixed>
     */
    public function &root(): array;

    /**
     * @param bool $destroy
     * @return bool
     */
    public function regenerate(bool $destroy = true): bool;

    /**
     * @return void
     */
    public function destroy(): void;
}

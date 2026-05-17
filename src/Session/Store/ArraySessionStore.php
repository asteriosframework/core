<?php declare(strict_types=1);

namespace Asterios\Core\Session\Store;

use Asterios\Core\Contracts\Session\Store\SessionStoreInterface;

final class ArraySessionStore implements SessionStoreInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private bool $started = false;

    private string $sessionId = 'array-session';

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        $this->started = true;
    }

    /**
     * @inheritDoc
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @inheritDoc
     */
    public function &root(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function regenerate(bool $destroy = true): bool
    {
        $this->start();

        if ($destroy)
        {
            $this->sessionId = bin2hex(random_bytes(16));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $this->data = [];
        $this->started = false;
        $this->sessionId = 'array-session';
    }

    /**
     * @return string
     */
    public function sessionId(): string
    {
        return $this->sessionId;
    }
}

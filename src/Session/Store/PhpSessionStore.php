<?php declare(strict_types=1);

namespace Asterios\Core\Session\Store;

use Asterios\Core\Contracts\Session\Store\SessionStoreInterface;

final class PhpSessionStore implements SessionStoreInterface
{
    /**
     * @inheritDoc
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }
    }

    /**
     * @inheritDoc
     */
    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * @return array<string, mixed>
     */
    public function &root(): array
    {
        if (!isset($_SESSION))
        {
            $_SESSION = [];
        }

        return $_SESSION;
    }

    /**
     * @inheritDoc
     */
    public function regenerate(bool $destroy = true): bool
    {
        $this->start();

        return session_regenerate_id($destroy);
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        if (!$this->isStarted())
        {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies'))
        {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ],
            );
        }

        session_destroy();
    }
}

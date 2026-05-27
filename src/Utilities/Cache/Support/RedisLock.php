<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Support;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Redis;

final class RedisLock implements LockInterface
{
    private bool $acquired = false;
    private readonly string $token;

    public function __construct(
        private readonly Redis $redis,
        private readonly string $key,
    ) {
        $this->token = bin2hex(random_bytes(16));
    }

    /**
     * @inheritDoc
     */
    public function acquire(int $ttl = 10): bool
    {
        $this->acquired = (bool) $this->redis->set(
            $this->key,
            $this->token,
            ['nx', 'ex' => $ttl]
        );

        return $this->acquired;
    }

    /**
     * @inheritDoc
     */
    public function release(): bool
    {
        $script = <<<'LUA'
if redis.call("get", KEYS[1]) == ARGV[1] then
    return redis.call("del", KEYS[1])
end
return 0
LUA;

        $result = $this->redis->eval($script, [$this->key, $this->token], 1);

        $this->acquired = false;

        return (bool) $result;
    }

    /**
     * @inheritDoc
     */
    public function isAcquired(): bool
    {
        return $this->acquired;
    }

    public function __destruct()
    {
        if ($this->acquired)
        {
            $this->release();
        }
    }
}
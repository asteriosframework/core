<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Serialization;

use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Exception\Utilities\Cache\SerializationException;
use Throwable;

final class PhpSerializer implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serialize(mixed $value): string
    {
        try
        {
            return serialize($value);
        }
        catch (Throwable $e)
        {
            throw new SerializationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function unserialize(string $payload): mixed
    {
        try
        {
            return unserialize($payload, [
                'allowed_classes' => true,
            ]);
        }
        catch (Throwable $e)
        {
            throw new SerializationException($e->getMessage(), 0, $e);
        }
    }
}

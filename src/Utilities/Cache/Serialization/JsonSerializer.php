<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Serialization;

use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Exception\Utilities\Cache\SerializationException;
use JsonException;

final class JsonSerializer implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serialize(mixed $value): string
    {
        try
        {
            return json_encode(
                $value,
                JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException $e)
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
            return json_decode(
                $payload,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException $e)
        {
            throw new SerializationException($e->getMessage(), 0, $e);
        }
    }
}

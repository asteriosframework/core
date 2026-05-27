<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Serialization;

use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Exception\Utilities\Cache\SerializationException;

final class IgbinarySerializer implements SerializerInterface
{
    public function __construct()
    {
        if (!function_exists('igbinary_serialize'))
        {
            throw new SerializationException('igbinary extension not installed.');
        }
    }

    /**
     * @inheritDoc
     */
    public function serialize(mixed $value): string
    {
        return igbinary_serialize($value);
    }

    /**
     * @inheritDoc
     */
    public function unserialize(string $payload): mixed
    {
        return igbinary_unserialize($payload);
    }
}
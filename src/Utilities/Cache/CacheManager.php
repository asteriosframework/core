<?php

declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache;

use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;
use Asterios\Core\Exception\Utilities\Cache\CacheException;

final class CacheManager
{
    /**
     * @var array<string, CacheDriverInterface>
     */
    private array $drivers = [];

    /**
     * @param array<string, CacheDriverInterface> $drivers
     */
    public function __construct(array $drivers = [])
    {
        foreach ($drivers as $name => $driver)
        {
            $this->register($name, $driver);
        }
    }

    public function register(
        string $name,
        CacheDriverInterface $driver
    ): self {
        $this->drivers[$name] = $driver;

        return $this;
    }

    public function driver(string $name): Cache
    {
        if (!isset($this->drivers[$name]))
        {
            throw new CacheException(
                sprintf('Cache driver "%s" is not registered.', $name)
            );
        }

        $driver = $this->drivers[$name];

        if (!$driver->isAvailable())
        {
            throw new CacheException(
                sprintf('Cache driver "%s" is not available.', $name)
            );
        }

        return new Cache($driver);
    }

    public function chain(array $driverNames): Cache
    {
        $drivers = [];

        foreach ($driverNames as $name)
        {
            if (!isset($this->drivers[$name]))
            {
                continue;
            }

            $driver = $this->drivers[$name];

            if ($driver->isAvailable())
            {
                $drivers[] = $driver;
            }
        }

        if ($drivers === [])
        {
            throw new CacheException(
                'No available cache drivers in chain.'
            );
        }

        return new Cache(
            new DriverChain($drivers)
        );
    }

    public function hasDriver(string $name): bool
    {
        return isset($this->drivers[$name]);
    }

    public function removeDriver(string $name): self
    {
        unset($this->drivers[$name]);

        return $this;
    }

    /**
     * @return array<string, CacheDriverInterface>
     */
    public function all(): array
    {
        return $this->drivers;
    }
}

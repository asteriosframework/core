<?php

declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;
use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Exception\Utilities\Cache\CacheException;
use Asterios\Core\Utilities\Cache\Drivers\ApcuDriver;
use Asterios\Core\Utilities\Cache\Drivers\FileDriver;
use Asterios\Core\Utilities\Cache\Drivers\MySqlDriver;
use Asterios\Core\Utilities\Cache\Drivers\RedisDriver;
use Asterios\Core\Utilities\Cache\Serialization\IgbinarySerializer;
use Asterios\Core\Utilities\Cache\Serialization\JsonSerializer;
use Asterios\Core\Utilities\Cache\Serialization\PhpSerializer;
use Redis;

final class CacheFactory
{
    public static function fromConfig(string $configGroup = 'default'): Cache
    {
        $config = Config::get('cache');

        $default = (object)$config->{$configGroup};

        $driver = $default->driver;
        $serializer = $default->serializer;
        $prefix = $default->prefix;
        $ttl = (int)$default->default_ttl;

        return match ($driver)
        {
            'redis' => new Cache(
                self::buildRedisDriver(
                    config: $config,
                    serializer: $serializer,
                    prefix: $prefix
                ),
                $ttl
            ),

            'apcu' => self::apcu(
                serializer: $serializer,
                prefix: $prefix,
                defaultTtl: $ttl
            ),

            'mysql' => self::mysql(
                configGroup: $config->mysql['config_group'],
                table: $config->mysql['table'],
                serializer: $serializer,
                prefix: $prefix,
                defaultTtl: $ttl
            ),

            'chain' => self::buildChain(
                config: $config,
                serializer: $serializer,
                prefix: $prefix,
                defaultTtl: $ttl
            ),

            default => self::file(
                path: $config->file['path'],
                serializer: $serializer,
                prefix: $prefix,
                defaultTtl: $ttl
            ),
        };
    }

    public static function serializer(
        string $type = 'php'
    ): SerializerInterface {
        return match ($type)
        {
            'json' => new JsonSerializer(),
            'igbinary' => new IgbinarySerializer(),
            default => new PhpSerializer(),
        };
    }

    public static function redis(
        Redis $redis,
        string $serializer = 'php',
        string $prefix = 'asterios:',
        int $defaultTtl = 3600,
    ): Cache {
        return new Cache(
            new RedisDriver(
                redis: $redis,
                serializer: self::serializer($serializer),
                prefix: $prefix,
            ),
            $defaultTtl
        );
    }

    public static function apcu(
        string $serializer = 'php',
        string $prefix = 'asterios:',
        int $defaultTtl = 3600,
    ): Cache {
        return new Cache(
            new ApcuDriver(
                serializer: self::serializer($serializer),
                prefix: $prefix,
            ),
            $defaultTtl
        );
    }

    public static function file(
        string $path,
        string $serializer = 'php',
        string $prefix = 'asterios:',
        int $defaultTtl = 3600,
    ): Cache {
        return new Cache(
            new FileDriver(
                cachePath: Asterios::getBasePath($path),
                serializer: self::serializer($serializer),
                prefix: $prefix,
            ),
            $defaultTtl
        );
    }

    public static function mysql(
        string $configGroup = 'default',
        string $table = 'cache_entries',
        string $serializer = 'php',
        string $prefix = 'asterios:',
        int $defaultTtl = 3600,
    ): Cache {
        return new Cache(
            new MySqlDriver(
                configGroup: $configGroup,
                table: $table,
                serializer: self::serializer($serializer),
                prefix: $prefix,
            ),
            $defaultTtl
        );
    }

    public static function chain(
        array $drivers,
        int $defaultTtl = 3600,
    ): Cache {
        return new Cache(
            new DriverChain($drivers),
            $defaultTtl
        );
    }

    public static function manager(
        array $drivers = []
    ): CacheManager {
        return new CacheManager($drivers);
    }

    public static function psr16(Cache $cache): Psr16CacheAdapter
    {
        return new Psr16CacheAdapter($cache);
    }

    private static function buildRedisDriver(
        object $config,
        string $serializer,
        string $prefix,
    ): CacheDriverInterface {
        $redis = new Redis();

        $redis->connect(
            $config->redis['host'],
            (int)$config->redis['port'],
            (float)$config->redis['timeout']
        );

        if (!empty($config->redis['password']))
        {
            $redis->auth($config->redis['password']);
        }

        $redis->select(
            (int)$config->redis['database']
        );

        return new RedisDriver(
            redis: $redis,
            serializer: self::serializer($serializer),
            prefix: $prefix,
        );
    }

    private static function buildChain(
        object $config,
        string $serializer,
        string $prefix,
        int $defaultTtl,
    ): Cache {
        $drivers = [];
        $serializerInstance = self::serializer($serializer);

        foreach ($config->chain['drivers'] as $driver)
        {
            $drivers[] = match ($driver)
            {
                'apcu' => new ApcuDriver(
                    serializer: $serializerInstance,
                    prefix: $prefix,
                ),

                'redis' => self::buildRedisDriver(
                    config: $config,
                    serializer: $serializer,
                    prefix: $prefix
                ),

                'file' => new FileDriver(
                    cachePath: Asterios::getBasePath($config->file['path']),
                    serializer: $serializerInstance,
                    prefix: $prefix,
                ),

                'mysql' => new MySqlDriver(
                    configGroup: $config->mysql['config_group'],
                    table: $config->mysql['table'],
                    serializer: $serializerInstance,
                    prefix: $prefix,
                ),

                default => throw new CacheException(
                    sprintf(
                        'Unsupported cache chain driver: %s',
                        $driver
                    )
                ),
            };
        }

        $drivers = array_filter($drivers);

        if ($drivers === [])
        {
            throw new CacheException(
                'Cache chain contains no valid drivers.'
            );
        }

        return new Cache(
            new DriverChain($drivers),
            $defaultTtl
        );
    }
}

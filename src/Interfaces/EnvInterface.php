<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvItemNotFoundException;
use Asterios\Core\Exception\EnvLoadException;

interface EnvInterface
{
    /**
     * @param string $item
     * @param string|int|bool|array|null $default
     * @return string|int|bool|array|null
     *
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function get(string $item, string|int|bool|array|null $default = null): string|int|bool|array|null;

    /**
     * @param string $item
     * @return string|int|bool|array|null
     *
     * @throws EnvException
     * @throws EnvLoadException
     * @throws EnvItemNotFoundException
     */
    public function getRequired(string $item): string|int|bool|array|null;

    /**
     * @param string $item
     * @param array $default
     * @return string[]
     *
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function getArray(string $item, array $default = []): array;

    /**
     * @param string $prefix
     * @return string[]
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function getArrayPrefixed(string $prefix): array;
}
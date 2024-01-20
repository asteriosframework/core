<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvItemNotFoundException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Interfaces\EnvInterface;

class Env implements EnvInterface
{
    private array $tmpEnv;

    /**
     * @param string $envFile
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function __construct(string $envFile = '.env')
    {
        if (empty($envFile))
        {
            throw new EnvException('.Parameter should not be empty!');
        }

        if (!is_file($envFile))
        {
            throw new EnvLoadException('Environment file "' . $envFile . '" is missing.');
        }

        if (!is_readable($envFile))
        {
            throw new EnvLoadException('Permission denied for "' . (realpath($envFile)) . '".');
        }

        $envFileContent = fopen(realpath($envFile), 'rb');

        if (!$envFileContent)
        {
            throw new EnvLoadException('fopen failed to open ' . (realpath($envFile)) . '!');
        }

        $this->tmpEnv = [];

        while (($line = fgets($envFileContent)) !== false)
        {
            $isLineComment = str_starts_with(trim($line), '#');

            if ($isLineComment || empty(trim($line)))
            {
                continue;
            }

            $hasNoLineComment = explode('#', $line, 2)[0];
            $envEx = preg_split('/(\s?)=(\s?)/', $hasNoLineComment);
            $envName = trim($envEx[0]);
            $envValue = $envEx[1] ?? '';

            $this->tmpEnv[$envName] = $envValue;
        }

        fclose($envFileContent);

        $this->load();
    }

    protected function load(): void
    {
        foreach ($this->tmpEnv as $name => $value)
        {
            putenv("$name=$value");

            $_ENV[$name] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function get(string $item, string|int|bool|array|null $default = null): string|int|bool|array|null
    {
        $itemValue = getenv($item);

        if (!$itemValue)
        {
            return $default;
        }

        $itemUnquotedValue = str_replace('"', '', $itemValue);

        $trimmedItemValue = trim($itemUnquotedValue);

        if ('' === $trimmedItemValue)
        {
            return $default;
        }

        if (!ctype_upper($trimmedItemValue))
        {
            $sanitizedItemValue = strtolower($trimmedItemValue);
        }
        else
        {
            $sanitizedItemValue = $trimmedItemValue;
        }

        return match ($sanitizedItemValue)
        {
            'false' => false,
            'true' => true,
            'null' => null,
            default => $sanitizedItemValue,
        };
    }

    /**
     * @inheritdoc
     */
    public function getRequired(string $item): string|int|bool|array|null
    {
        $itemValue = $this->get($item, false);

        if (false === $itemValue)
        {
            throw new EnvItemNotFoundException('Required environment variable ' . $item . ', not found!');
        }

        return $itemValue;
    }

    /**
     * @inheritdoc
     */
    public function getArray(string $item, array $default = []): array
    {
        $itemValue = $this->get($item);

        if (null === $itemValue)
        {
            return $default;
        }

        return explode(',', $itemValue);
    }

    /**
     * @inheritdoc
     */
    public function getArrayPrefixed(string $prefix): array
    {
        if (empty($prefix))
        {
            throw new EnvException('You must provide a non-empty prefix to search for.');
        }

        $results = [];

        foreach (array_keys($_ENV) as $name)
        {
            if ($this->startsWith($name, $prefix))
            {
                $nameAfterPrefix = substr($name, strlen($prefix));
                $results[$nameAfterPrefix] = $this->get($name);
            }
        }

        return $results;
    }

    private function startsWith($haystack, $needle): bool
    {
        return (strpos($haystack, $needle) === 0);
    }
}
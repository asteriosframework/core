<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvItemNotFoundException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Interfaces\EnvInterface;
use Asterios\Core\Traits\FileTrait;

class Env implements EnvInterface
{
    use FileTrait;

    protected string $envFile;

    /**
     * @param string $envFile
     */
    public function __construct(string $envFile = '.env')
    {
        $this->envFile = $envFile;
    }

    /**
     * @return void
     * @throws EnvException
     * @throws EnvLoadException
     */
    protected function load(): void
    {
        if (empty($this->envFile))
        {
            throw new EnvException('Parameter should not be empty!');
        }

        $file = $this->getFile();

        $isFile = $file->is_file($this->envFile);

        if (false === $isFile)
        {
            throw new EnvLoadException('Environment file "' . $this->envFile . '" is missing.');
        }

        $isEnvFileReadable = $file->isReadable($this->envFile);

        if (false === $isEnvFileReadable)
        {
            throw new EnvLoadException('Permission denied for "' . (realpath($this->envFile)) . '".');
        }

        $envFileContent = $file->open(realpath($this->envFile));

        if (false === $envFileContent)
        {
            throw new EnvLoadException('File::class failed to open ' . (realpath($this->envFile)) . '!');
        }

        $tmpEnv = [];

        while (($line = $file->gets($envFileContent)) !== false)
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

            $tmpEnv[$envName] = $envValue;
        }

        fclose($envFileContent);

        foreach ($tmpEnv as $name => $value)
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
        $this->load();

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

        $this->load();

        $results = [];

        foreach (array_keys($_ENV) as $name)
        {
            if (str_starts_with($name, $prefix))
            {
                $nameAfterPrefix = substr($name, strlen($prefix));
                $results[$nameAfterPrefix] = $this->get($name);
            }
        }

        return $results;
    }
}
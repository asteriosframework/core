<?php

declare(strict_types=1);

namespace Asterios\Core;

use JsonException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

final class Logger implements LoggerInterface
{
    private string $logDirectory;
    private string $logFilename;
    private string $dateFormat;
    private string $logFormat;

    public function __construct(
        string $logDirectory,
        string $logFilename = 'app',
        string $dateFormat = 'Ymd',
        string $logFormat = 'Y-m-d H:i:s'
    )
    {
        $this->logDirectory = rtrim($logDirectory, DIRECTORY_SEPARATOR);
        $this->logFilename = $logFilename;
        $this->dateFormat = $dateFormat;
        $this->logFormat = $logFormat;
    }

    /**
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     * @throws JsonException
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param $level
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     * @throws JsonException
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->ensureLogDirectoryExists();

        $timestamp = date($this->logFormat);
        $level = strtoupper((string)$level);
        $env = strtolower(Asterios::getEnvironment());

        $interpolatedMessage = $this->interpolate((string)$message, $context);
        $contextJson = $this->normalizeContext($context);

        $line = sprintf(
            '[%s] %s.%s: %s%s%s',
            $timestamp,
            $env,
            $level,
            $interpolatedMessage,
            $contextJson !== '' ? ' ' : '',
            $contextJson
        );

        $this->writeToFile($line);
    }

    private function ensureLogDirectoryExists(): void
    {
        if (!is_dir($this->logDirectory))
        {
            if (!mkdir($concurrentDirectory = $this->logDirectory, 0775, true)
                && !is_dir($concurrentDirectory))
            {
                throw new RuntimeException(
                    sprintf('Unable to create log directory: %s', $this->logDirectory)
                );
            }
        }
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];

        foreach ($context as $key => $value)
        {
            if (is_scalar($value) || $value instanceof \Stringable)
            {
                $replace['{' . $key . '}'] = (string)$value;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * @throws JsonException
     */
    private function normalizeContext(array $context): string
    {
        if ($context === [])
        {
            return '';
        }

        return json_encode(
            $context,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    private function writeToFile(string $line): void
    {
        $filePath = $this->buildLogFilePath();

        $result = @file_put_contents(
            $filePath,
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($result === false)
        {
            throw new RuntimeException(
                sprintf('Unable to write to log file: %s', $filePath)
            );
        }
    }

    private function buildLogFilePath(): string
    {
        $date = date($this->dateFormat);

        return sprintf(
            '%s%s%s-%s.log',
            $this->logDirectory,
            DIRECTORY_SEPARATOR,
            $this->logFilename,
            $date
        );
    }

    /**
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     * @throws JsonException
     */
    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     * @throws JsonException
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     * @throws JsonException
     */
    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     * @throws JsonException
     */
    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     * @throws JsonException
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}

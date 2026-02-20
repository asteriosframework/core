<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Asterios\Core\Exception\LoggerException;

interface LoggerInterface
{
    /**
     * @param string|null $logfileName
     * @param string|null $logDirectory
     * @return self
     */
    public static function forge(?string $logfileName = null, ?string $logDirectory = null): self;

    /**
     * @return self
     * @throws LoggerException
     */
    public function createLogDirectory(): self;

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self;

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws LoggerException
     */
    public function info(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws LoggerException
     */
    public function notice(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws LoggerException
     */
    public function debug(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws LoggerException
     */
    public function warning(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws LoggerException
     */
    public function error(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws LoggerException
     */
    public function fatal(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws LoggerException
     */
    public function critical(string $message, array $context = []): void;

    /**
     * @param array $args
     * @return void
     * @throws LoggerException
     */
    public function writeLog(array $args = []): void;

    /**
     * @param string $pathToConvert
     * @return string
     * @noinspection PhpUnused
     */
    public function absToRealPath(string $pathToConvert): string;



}
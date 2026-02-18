<?php

declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\ConfigLoadException;
use JsonException;

class Logger
{
    protected array $options = [
        'dateFormat' => 'Ymd',
        'logFormat' => 'Y-m-d H:i:s',
        'logDirectory' => null,
        'logFilename' => null,
    ];

    /**
     * @param string|null $logFileName
     * @param string|null $logDirectory
     */
    public function __construct(?string $logFileName = null, string $logDirectory = null)
    {
        if (null !== $logFileName)
        {
            $this->setOptions(['logFilename' => $logFileName]);
        }

        if (null !== $logDirectory)
        {
            $this->setOptions(['logDirectory' => $logDirectory]);
        }
    }

    /**
     * @param string|null $logfileName
     * @param string|null $logDirectory
     * @return self
     */
    public static function forge(?string $logfileName = null, string $logDirectory = null): self
    {
        return new self($logfileName, $logDirectory);
    }

    /**
     * @return $this
     * @throws Exception\ConfigLoadException
     */
    public function createLogDirectory(): self
    {
        $logDirectory = $this->options['logDirectory'];

        if (null === $logDirectory)
        {
            $logDirectory = Config::get('default', 'logger.log_dir');
        }

        if (!File::forge()
            ->directory_exists($logDirectory))
        {
            File::forge()
                ->create_directory($logDirectory);
        }

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws JsonException
     * @throws ConfigLoadException
     */
    public function info(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        $this->writeLog([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'INFO',
            'context' => $context,
        ]);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws JsonException
     * @throws ConfigLoadException
     */
    public function notice(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        $this->writeLog([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'NOTICE',
            'context' => $context,
        ]);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws JsonException
     * @throws ConfigLoadException
     */
    public function debug(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        $this->writeLog([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'DEBUG',
            'context' => $context,
        ]);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws JsonException
     * @throws ConfigLoadException
     */
    public function warning(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        $this->writeLog([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'WARNING',
            'context' => $context,
        ]);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws JsonException
     * @throws ConfigLoadException
     */
    public function error(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        $this->writeLog([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'ERROR',
            'context' => $context,
        ]);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws JsonException
     * @throws ConfigLoadException
     */
    public function fatal(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        $this->writeLog([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'FATAL',
            'context' => $context,
        ]);
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     * @throws JsonException
     * @throws ConfigLoadException
     */
    public function critical(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        $this->writeLog([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'CRITICAL',
            'context' => $context,
        ]);
    }

    /**
     * @param array $args
     * @return void
     * @throws Exception\ConfigLoadException
     * @throws JsonException
     */
    public function writeLog(array $args = []): void
    {
        $this->createLogDirectory();

        $fileHandle = $this->openLog();

        if (false === $fileHandle)
        {
            return;
        }

        $time = date($this->options['logFormat']);

        $context = json_encode($args['context'], JSON_THROW_ON_ERROR);

        $currentEnv = strtolower(Asterios::getEnvironment());

        $timeLog = '['.$time.'] ';
        $severityLog = $currentEnv . '.';
        $severityLog .= is_null($args['severity']) ? 'N/A' : $args['severity'];
        $messageLog = is_null($args['message']) ? 'N/A' : (string)($args['message']);
        $contextLog = empty($args['context']) ? '' : (string)($context);

        $fileContent = $timeLog.$severityLog.': '.$messageLog.' '.$contextLog. PHP_EOL;

        fwrite($fileHandle, $fileContent);
        fclose($fileHandle);
    }

    /**
     * @return false|resource
     * @throws ConfigLoadException
     */
    private function openLog(): false
    {
        $openFile = $this->getLogfileName();

        $handle = fopen($openFile, 'ab');

        if (!$handle)
        {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('Could not create/open log file ' . $openFile);
        }

        return $handle;
    }

    /**
     * @param string $pathToConvert
     * @return string
     * @noinspection PhpUnused
     */
    public function absToRealPath(string $pathToConvert): string
    {
        $pathAbs = str_replace(['/', '\\'], '/', $pathToConvert);
        $documentRoot = str_replace(['/', '\\'], '/', $_SERVER['DOCUMENT_ROOT']);

        return $_SERVER['SERVER_NAME'] . str_replace($documentRoot, '', $pathAbs);
    }

    /**
     * @return string
     * @throws ConfigLoadException
     */
    protected function getLogfileName(): string
    {
        $config = Config::get('default', 'logger');

        $logDirectory = $this->options['logDirectory'];
        $logFile = $this->options['logFilename'];

        if (null === $logDirectory)
        {
            $logDirectory = $config->log_dir;
        }

        if (null === $logFile)
        {
            $logFile = $config->log_file;
        }

        $time = date($this->options['dateFormat']);

        return $logDirectory . DIRECTORY_SEPARATOR . $logFile . '-' . $time . '.log';
    }
}

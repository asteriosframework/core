<?php

declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\LoggerInterface;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\LoggerException;
use JsonException;

class Logger implements LoggerInterface
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
     * @inheritDoc
     */
    public static function forge(?string $logfileName = null, string $logDirectory = null): self
    {
        return new self($logfileName, $logDirectory);
    }

    /**
     * @inheritDoc
     */
    public function createLogDirectory(): self
    {
        $logDirectory = $this->options['logDirectory'];

        if (null === $logDirectory)
        {
            try
            {
                $logDirectory = Config::get('default', 'logger.log_dir');
                if (!File::forge()
                    ->directory_exists($logDirectory))
                {
                    File::forge()
                        ->create_directory($logDirectory);
                }

            }
            catch (ConfigLoadException $e)
            {
                throw new LoggerException($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function writeLog(array $args = []): void
    {
        try
        {
            $this->createLogDirectory();
            $fileHandle = $this->openLog();

            if (false === $fileHandle)
            {
                return;
            }

            $time = date($this->options['logFormat']);

            try
            {
                $context = json_encode($args['context'], JSON_THROW_ON_ERROR);
            }
            catch (JsonException $e)
            {
                throw new LoggerException($e->getMessage());
            }

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
        catch (ConfigLoadException $e)
        {
            throw new LoggerException($e->getMessage());
        }
    }

    /**
     * @return false|resource
     * @throws ConfigLoadException
     */
    private function openLog()
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
     * @inheritDoc
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

<?php declare(strict_types=1);

namespace Asterios\Core;

use RuntimeException;

class Logger
{
    protected static $log_file;

    protected static $file;

    protected static $options = [
        'dateFormat' => 'Ymd',
        'logFormat' => 'Y-m-d H:i:s',
    ];

    /**
     * @throws RuntimeException
     */
    public static function create_log_directory(): void
    {
        $log_dir = Config::get('default', 'logger.log_dir');

        if (!File::forge()
            ->directory_exists($log_dir))
        {
            File::forge()
                ->create_directory($log_dir);
        }
    }

    /**
     * Set logging options (optional)
     * @param array $options Array of settable options
     *
     * Options:
     *  [
     *      'dateFormat' => 'value of the date format the .txt file should be saved int'
     *      'logFormat' => 'value of the date format each log event should be saved int'
     *  ]
     */
    public static function set_options($options = []): void
    {
        static::$options = array_merge(static::$options, $options);
    }

    /**
     * Info method (write info message)
     *
     * Used for e.g.: "The user example123 has created a post".
     *
     * @param string $message Descriptive text of the debug
     * @param array $context Array to expend the message's meaning
     * @return void
     * @throws RuntimeException
     */
    public static function info(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        static::write_log([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'INFO',
            'context' => $context,
        ]);
    }

    /**
     * Notice method (write notice message)
     *
     * Used for e.g.: "The user example123 has created a post".
     *
     * @param string $message Descriptive text of the debug
     * @param array $context Array to expend the message's meaning
     * @return void
     * @throws RuntimeException
     */
    public static function notice(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        static::write_log([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'NOTICE',
            'context' => $context,
        ]);
    }

    /**
     * Debug method (write debug message)
     *
     * Used for debugging, could be used instead of echo values
     *
     * @param string $message Descriptive text of the debug
     * @param array $context Array to expend the message's meaning
     * @return void
     * @throws RuntimeException
     */
    public static function debug(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        static::write_log([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'DEBUG',
            'context' => $context,
        ]);
    }

    /**
     * Warning method (write warning message)
     *
     * Used for warnings which is not fatal to the current operation
     *
     * @param string $message Descriptive text of the warning
     * @param array $context Array to expend the message's meaning
     * @return void
     * @throws RuntimeException
     */
    public static function warning(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        static::write_log([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'WARNING',
            'context' => $context,
        ]);
    }

    /**
     * Error method (write error message)
     *
     * Used for e.g. file not found
     *
     * @param string $message Descriptive text of the error
     * @param array $context Array to expend the message's meaning
     * @return void
     * @throws RuntimeException
     */
    public static function error(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        static::write_log([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'ERROR',
            'context' => $context,
        ]);
    }

    /**
     * Fatal method (write fatal message)
     *
     * Used for e.g. database unavailable, system shutdown
     *
     * @param string $message Descriptive text of the error
     * @param array $context Array to expend the message's meaning
     * @return void
     * @throws RuntimeException
     */
    public static function fatal(string $message, array $context = []): void
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        static::write_log([
            'message' => $message,
            'bt' => $bt,
            'severity' => 'FATAL',
            'context' => $context,
        ]);
    }

    /**
     * Write to log file
     * @param array $args Array of message (for log file), line (of log method execution), severity (for log file) and displayMessage (to display on frontend for the used)
     * @return void
     * @throws Exception\ConfigLoadException
     */
    public static function write_log(array $args = []): void
    {
        static::create_log_directory();

        $handle = self::open_log();

        if (!$handle)
        {
            return;
        }

        /** @var false|string $time */
        $time = date(static::$options['logFormat']);

        $context = json_encode($args['context']);

        $timeLog = (false === $time) ? "[N/A] " : "[{$time}] ";
        $severityLog = is_null($args['severity']) ? "[N/A]" : "[{$args['severity']}]";
        $messageLog = is_null($args['message']) ? "N/A" : (string)($args['message']);
        $contextLog = empty($args['context']) ? "" : (string)($context);

        fwrite($handle, "{$timeLog}{$severityLog} - {$messageLog} {$contextLog}" . PHP_EOL);

        static::close_file();
    }

    /**
     * Open log file
     * @return false|resource
     * @throws Exception\ConfigLoadException
     */
    private static function open_log()
    {
        $open_file = static::get_logfile_name();

        $handle = fopen($open_file, 'ab');

        if (!$handle)
        {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('Could not create/open log file ' . $open_file);
        }

        return $handle;
    }

    /**
     *  Close file stream
     */
    public static function close_file(): void
    {
        if (static::$file)
        {
            fclose(static::$file);
        }
    }

    /**
     * Convert absolute path to relative url (using UNIX directory separators)
     *
     * E.g.:
     *      Input:      D:\development\htdocs\public\todo-list\index.php
     *      Output:     localhost/todo-list/index.php
     */
    public static function abs_to_real_path(string $pathToConvert): string
    {
        $pathAbs = str_replace(['/', '\\'], '/', $pathToConvert);
        $documentRoot = str_replace(['/', '\\'], '/', $_SERVER['DOCUMENT_ROOT']);

        return $_SERVER['SERVER_NAME'] . str_replace($documentRoot, '', $pathAbs);
    }

    /**
     * @throws Exception\ConfigLoadException
     */
    protected static function get_logfile_name(): string
    {
        $config = Config::get('default', 'logger');
        $log_dir = $config->log_dir;
        $log_file = $config->log_file;

        $time = date(static::$options['dateFormat']);

        return $log_dir . DIRECTORY_SEPARATOR . $log_file . '-' . $time . '.log';
    }
}
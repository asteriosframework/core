<?php declare(strict_types=1);

namespace Asterios\Core;

class Debug
{
    /**
     * This method will generate a human readable output to the browser
     */
    public static function dump(): void
    {
        $called = [];
        $backtrace = debug_backtrace();

        // locate the first file entry that isn't this class itself
        foreach ($backtrace as $stack => $trace)
        {
            if (isset($trace['file']))
            {
                if (false !== strpos($trace['file'], 'src/Debug.php'))
                {
                    $called = $backtrace[$stack + 1];
                }
                else
                {
                    $called = $trace;
                }
                break;
            }
        }

        if (isset($called['file'], $called['line']))
        {
            $arguments = func_get_args();

            $output = '<div style="font-size: 13px;background: #eeeeee !important; border:1px solid #cccccc; color: #000 !important; padding:10px;">';
            $output .= '<h1 style="border-bottom: 1px solid #cccccc; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">' . $called['file'] .
                ' @ line: ' . $called['line'] . '</h1>';
            $output .= '<pre style="overflow:auto;font-size:100%;">';

            $count = count($arguments);

            for ($i = 1; $i <= $count; $i++)
            {
                $output .= '<strong>Variable #' . $i . ':</strong>' . PHP_EOL;
                $output .= self::prettifier('', $arguments[$i - 1]);
                $output .= PHP_EOL . PHP_EOL;
            }

            $output .= '</pre>';
            $output .= '</div>';

            echo $output;
        }
    }

    /**
     * @param mixed $expression
     */
    public static function export($expression): void
    {
        $called = [];
        $backtrace = debug_backtrace();

        // locate the first file entry that isn't this class itself
        foreach ($backtrace as $stack => $trace)
        {
            if (isset($trace['file']))
            {
                if (false !== strpos($trace['file'], 'core/debug.php'))
                {
                    $called = $backtrace[$stack + 1];
                }
                else
                {
                    $called = $trace;
                }
                break;
            }
        }

        if (isset($called['file'], $called['line']))
        {
            $output = '<div style="font-size: 13px;background: #eeeeee !important; border:1px solid #cccccc; color: #000 !important; padding:10px;">';
            $output .= '<h1 style="border-bottom: 1px solid #cccccc; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">' . $called['file'] .
                ' @ line: ' . $called['line'] . '</h1>';
            $output .= '<pre style="overflow:auto;font-size:100%;">';

            $output .= var_export($expression, true);

            $output .= '</pre>';
            $output .= '</div>';

            echo $output;
        }
    }

    /**
     * Output a backtrace
     * @param mixed $trace
     */
    public static function backtrace($trace = null): void
    {
        $trace || $trace = debug_backtrace();

        static::dump($trace);
    }

    /**
     * Prints a list of all currently declared classes.
     * If Param is not set or false, only the Asterios own classes will be returned.
     */
    public static function classes(bool $all = false): void
    {
        $loaded_classes = get_declared_classes();
        $return_classes = [];

        if (false === $all)
        {
            foreach ($loaded_classes as $value)
            {
                if (stripos($value, 'Asterios') !== false)
                {
                    $return_classes[] = $value;
                }
            }
        }
        else
        {
            foreach ($loaded_classes as $value)
            {
                $return_classes[] = $value;
            }
        }

        static::dump($return_classes);
    }

    /**
     * Prints a list of all currently declared interfaces (PHP5, PHP7 only).
     */
    public static function interfaces(bool $all = false): void
    {
        $loaded_interfaces = get_declared_interfaces();
        $return_interfaces = [];

        if (false === $all)
        {
            foreach ($loaded_interfaces as $value)
            {
                if (stripos($value, 'Asterios') !== false)
                {
                    $return_interfaces[] = $value;
                }
            }
        }
        else
        {
            foreach ($loaded_interfaces as $value)
            {
                $return_interfaces[] = $value;
            }
        }

        static::dump($return_interfaces);
    }

    /**
     * Prints a list of all currently included (or required) files.
     */
    public static function includes(): void
    {
        static::dump(get_included_files());
    }

    /**
     * Prints a list of all currently defined constants.
     */
    public static function constants(): void
    {
        static::dump(get_defined_constants());
    }

    /**
     * Prints a list of all currently loaded PHP extensions.
     */
    public static function extensions(): void
    {
        static::dump(get_loaded_extensions());
    }

    /**
     * Prints a list of the configuration settings read from <i>php.ini</i>
     */
    public static function get_ini(?string $value = null): void
    {
        static::dump(ini_get($value));
    }

    /**
     * Prints a list of the configuration settings read from <i>php.ini</i>
     */
    public static function get_ini_all(?string $extension = null): void
    {
        static::dump(ini_get_all($extension));
    }

    /**
     * Formats the given $var's output in a nice looking, Foldable interface.
     *
     * @param string $name the name of the var
     * @param mixed $var the variable
     * @param int $level the indentation level
     * @param string $indent_char the indentation character
     * @param string $scope
     * @return string    the formatted string.
     */
    private static function prettifier(string $name, $var, int $level = 0, string $indent_char = '&nbsp;&nbsp;&nbsp;&nbsp;', string $scope = ''): string
    {
        $return = str_repeat($indent_char, $level);
        if (is_array($var))
        {
            $return .= "<i>{$scope}</i> <strong>{$name}</strong>";
            $return .= " (Array, " . count($var) . " element" . (count($var) !== 1 ? "s" : "") . ")";

            $return .= "\n";

            $sub_return = '';

            foreach ($var as $key => $val)
            {
                $sub_return .= self::prettifier($key, $val, $level + 1);
            }
            $return .= $sub_return;
        }
        elseif (is_string($var))
        {
            $return .= "<i>{$scope}</i> <strong>{$name}</strong> (String): <span style=\"color:#E00000;\">\"" . htmlentities($var) . "\"</span> (" .
                strlen($var) . " characters)\n";
        }
        elseif (is_float($var))
        {
            $return .= "<i>{$scope}</i> <strong>{$name}</strong> (Float): {$var}\n";
        }
        elseif (is_int($var))
        {
            $return .= "<i>{$scope}</i> <strong>{$name}</strong> (Integer): {$var}\n";
        }
        elseif (is_null($var))
        {
            $return .= "<i>{$scope}</i> <strong>{$name}</strong> : null\n";
        }
        elseif (is_bool($var))
        {
            $return .= "<i>{$scope}</i> <strong>{$name}</strong> (Boolean): " . ($var ? 'true' : 'false') . "\n";
        }
        elseif (is_object($var))
        {
            // dirty hack to get the object id
            ob_start();
            var_dump($var);
            $contents = ob_get_clean();

            // process it based on the xdebug presence and configuration
            if (extension_loaded('xdebug') && ini_get('xdebug.overload_var_dump') === '1')
            {
                if (ini_get('html_errors'))
                {
                    preg_match('~(.*?)\)\[<i>(\d+)(.*)~', $contents, $matches);
                }
                else
                {
                    preg_match('~class (.*?)#(\d+)(.*)~', $contents, $matches);
                }
            }
            else
            {
                preg_match('~object\((.*?)#(\d+)(.*)~', $contents, $matches);
            }

            $rvar = new \ReflectionObject($var);
            $return .= "<i>{$scope}</i> <strong>{$name}</strong> (Object #" . $matches[2] . "): " . get_class($var);

            $return .= "\n";

            $sub_return = '';
            foreach ($rvar->getProperties() as $prop)
            {
                if ($prop->isPublic())
                {
                    $prop->setAccessible(true);
                }
                if ($prop->isPrivate())
                {
                    $scope = 'private';
                }
                elseif ($prop->isProtected())
                {
                    $scope = 'protected';
                }
                else
                {
                    $scope = 'public';
                }

                $sub_return .= self::prettifier($prop->name, $prop->getValue($var), $level + 1, $indent_char, $scope);
            }

            $return .= $sub_return;
        }
        else
        {
            $return .= "<i>{$scope}</i> <strong>{$name}</strong>: {$var}\n";
        }

        return $return;
    }
}

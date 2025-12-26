<?php

declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\SingletonInterface;
use Asterios\Core\Contracts\StrInterface;
use Asterios\Core\Exception\StrRandomBytesException;
use Random\RandomException;

class Str implements StrInterface, SingletonInterface
{
    /** @var Str */
    private static $str;

    public static function getInstance(): Str
    {
        if (self::$str === null)
        {
            self::$str = new self();
        }

        return self::$str;
    }

    protected function __clone()
    {
    }

    protected function __construct()
    {
    }

    /**
     * @param int|float|string|bool|null $value
     * @param string $characters
     * @return int|float|string|bool|null
     */
    public function trim($value, string $characters = " \n\r\t\v\x00")
    {
        return is_string($value) ? trim($value, $characters) : $value;
    }

    public function startsWith(string $string, string $start, bool $ignoreCase = false): bool
    {
        return (bool)preg_match('/^' . preg_quote($start, '/') . '/m' . ($ignoreCase ? 'i' : ''), $string);
    }

    public function endsWith(string $string, string $end, bool $ignoreCase = false): bool
    {
        return (bool)preg_match('/' . preg_quote($end, '/') . '$/m' . ($ignoreCase ? 'i' : ''), $string);
    }

    public function sub(string $string, int $start, ?int $length = null, ?string $encoding = 'UTF-8'): string
    {
        // substr functions don't parse null correctly
        $length = is_null($length) ? (function_exists('mb_substr') ? mb_strlen($string, $encoding) : strlen($string)) - $start : $length;

        return function_exists('mb_substr') ? mb_substr($string, $start, $length, $encoding) : substr($string, $start, $length);
    }

    public function length(string $string, ?string $encoding = null): int
    {
        $encoding || $encoding = Asterios::getEncoding();

        return function_exists('mb_strlen') ? mb_strlen($string, $encoding) : strlen($string);
    }

    public function lower(string $string, ?string $encoding = null): string
    {
        $encoding || $encoding = Asterios::getEncoding();

        return function_exists('mb_strtolower') ? mb_strtolower($string, $encoding) : strtolower($string);
    }

    public function upper(string $string, ?string $encoding = null): string
    {
        $encoding || $encoding = Asterios::getEncoding();

        return function_exists('mb_strtoupper') ? mb_strtoupper($string, $encoding) : strtoupper($string);
    }

    /**
     * @param string $type
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function random(string $type = 'alnum', int $length = 16): string
    {
        switch ($type)
        {
            case 'basic':
                return (string)mt_rand();

            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
            case 'distinct':
            case 'hexdec':
            default:
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    default:
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    case 'numeric':
                        $pool = '0123456789';
                        break;

                    case 'nozero':
                        $pool = '123456789';
                        break;

                    case 'distinct':
                        $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                        break;

                    case 'hexdec':
                        $pool = '0123456789abcdef';
                        break;
                }

                $string = '';
                for ($i = 0; $i < $length; $i++)
                {
                    $string .= substr($pool, random_int(0, strlen($pool) - 1), 1);
                }

                return $string;
            case 'unique':
                return md5(uniqid((string)mt_rand(), true));
            case 'sha1':
                return sha1(uniqid((string)mt_rand(), true));
            case 'uuid':
                $pool = ['8', '9', 'a', 'b'];

                return sprintf(
                    '%s-%s-4%s-%s%s-%s',
                    self::random('hexdec', 8),
                    self::random('hexdec', 4),
                    self::random('hexdec', 3),
                    $pool[array_rand($pool)],
                    self::random('hexdec', 3),
                    self::random('hexdec', 12)
                );
        }
    }

    public function isJson(string $value): bool
    {
        json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @throws \Exception
     */
    public function isXml(string $stringing): bool
    {
        $internal_errors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        $result = false !== simplexml_load_string($stringing);
        libxml_use_internal_errors($internal_errors);

        return $result;
    }

    public function isSerialized(string $value): bool
    {
        /** @noinspection UnserializeExploitsInspection */
        $array = @unserialize($value);

        return !(false === $array && $value !== 'b:0;');
    }

    public function isHtml(string $value): bool
    {
        return strlen(strip_tags($value)) < strlen($value);
    }

    public function filterKeys(array $array, array $keys, bool $remove = false): array
    {
        $return = [];

        foreach ($keys as $key)
        {
            if (array_key_exists($key, $array))
            {
                $remove || ($return[$key] = $array[$key]);

                if ($remove)
                {
                    unset($array[$key]);
                }
            }
        }

        return $remove ? $array : $return;
    }


    /**
     * @inheritDoc
     */
    public function generateBase32Secret(int $length = 16): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        try
        {
            $randomBytes = random_bytes($length);
        }
        // @codeCoverageIgnoreStart
        catch (RandomException $e)
        {
            throw new StrRandomBytesException($e->getMessage());
        }
        /// @codeCoverageIgnoreEnd
        $binaryString = '';

        foreach (str_split($randomBytes) as $char)
        {
            $binaryString .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($binaryString, 5) as $chunk)
        {
            $chunk = str_pad($chunk, 5, '0');
            $encoded .= $alphabet[bindec($chunk)];
        }

        return $encoded;
    }
}

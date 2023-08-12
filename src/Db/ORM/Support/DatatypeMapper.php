<?php

declare(strict_types=1);

namespace Asterios\Core\Db\ORM\Support;

use Asterios\Core\Str;

/**
 * @method static string dbTypeVarchar()
 * @method static string castVarchar($value)
 * @method static string dbTypeInt()
 * @method static int castInt($value)
 * @method static string dbTypeSmallint()
 * @method static int castSmallInt($value);
 * @method static string dbTypeBigint()
 * @method static int castBigint($value)
 * @method static string dbTypeBoolean()
 * @method static bool castBoolean($value)
 * @method static string dbTypeTinyint()
 * @method static bool castTinyint($value)
 * @method static string dbTypeDecimal()
 * @method static string castDecimal($value)
 * @method static string dbTypeDate()
 * @method static DateTime castDate($value)
 */
final class DatatypeMapper
{
    /**
     * @var array<string,string>
     */
    private static array $db_datatypes = [
        'varchar' => 'string',
        'int' => 'int',
        'smallint' => 'int',
        'bigint' => 'string',
        'boolean' => 'bool',
        'tinyint' => 'bool',
        'decimal' => 'string',
        'date' => 'datetime',
        'time' => 'datetime',
        'datetime' => 'datetime',
        'datetimetz' => 'datetime',
        'text' => 'string',
        'float' => 'float',
        'guid' => 'string',
    ];

    /**
     * @var array<string,string>
     */
    private static array $casts = [
        'string' => 'strval',
        'int' => 'intval',
        'bool' => 'boolval',
        'float' => 'floatval',
        'datetime' => 'date_create',
    ];

    /**
     * @var string[]
     */
    private static array $methodPrefix = [
        'dbType',
        'cast',
    ];

    /**
     * @param string $method
     * @param array $parameters
     * @return null|string|int|bool|float|\DateTime
     * @throws \BadMethodCallException
     */
    public static function __callStatic(string $method, array $parameters): null|string|int|bool|float|\DateTime
    {
        $result = null;

        /** @var Str $str */
        $str = Str::getInstance();

        foreach (self::$methodPrefix as $prefix)
        {
            if ($str->startsWith($method, $prefix))
            {

                $datatype = $str->lower($str->sub($method, $str->length($prefix)));

                $result = match ($prefix)
                {
                    'dbType' => array_key_exists($datatype, self::$db_datatypes)
                    ? self::$db_datatypes[$datatype] : self::badMethod($method),

                    'cast' => self::phpCast($datatype, $method, $parameters),

                    default => self::badMethod($method)
                };
            }
        }

        if ($result === null)
        {
            self::badMethod($method);
        }

        return $result;
    }

    /**
     * @param string $datatype
     * @param string $method
     * @param string[] $parameters
     * @return null|string|int|bool|float|\DateTime
     * @throws \BadMethodCallException
     */
    private static function phpCast(string $datatype, string $method, array $parameters): null|string|int|bool|float|\DateTime
    {
        $result = null;

        if (array_key_exists($datatype, self::$db_datatypes))
        {
            switch ($datatype)
            {
                case 'date':
                    /** @var bool|\DateTime $date */
                    $date = call_user_func_array(self::$casts[self::$db_datatypes[$datatype]], $parameters);
                    if (false !== $date)
                    {
                        $result = $date;
                    }
                    break;
                default:
                    $result = call_user_func_array(self::$casts[self::$db_datatypes[$datatype]], $parameters);
            }
        }

        if ($result === null)
        {
            self::badMethod($method);
        }

        return $result;
    }

    /**
     * @param string $method
     * @throws \BadMethodCallException
     * @return void
     */
    private static function badMethod(string $method): void
    {
        throw new \BadMethodCallException('Method ' . $method . ' not found.');
    }
}
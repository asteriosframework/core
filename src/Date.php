<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\DateException;
use DateTime;
use DateTimeZone;
use Exception;

class Date
{
    public const DEFAULT_TIMEZONE = 'Europe/Berlin';

    /**
     * @deprecated Use non-static method instead
     */
    public static function set_timezone(string $timezone): void
    {
        date_default_timezone_set($timezone);
    }

    /**
     * @deprecated Use non-static method instead
     */
    public static function get_timezone(): string
    {
        return date_default_timezone_get();
    }

    /**
     * @throws Exception
     * @deprecated Use non-static method instead
     *
     */
    public static function days_in_month(int $month, int $year): string
    {
        $date = sprintf('%d-%d-01', $year, $month);

        return (new DateTime($date))->format('t');
    }

    public function setTimezone(string $timezone): void
    {
        date_default_timezone_set($timezone);
    }

    public function getTimezone(): string
    {
        return date_default_timezone_get();
    }

    /**
     * @throws DateException
     */
    public function daysInMonth(int $month, int $year): string
    {
        try
        {
            $date = sprintf('%d-%d-01', $year, $month);

            return (new DateTime($date, new DateTimeZone(self::DEFAULT_TIMEZONE)))->format('t');

        }
        catch (Exception $e)
        {
            throw new DateException($e->getMessage());
        }

    }

    /**
     * @throws DateException
     */
    public function getTimestamp(string $date = 'now'): int
    {
        try
        {
            return (new DateTime($date, new DateTimeZone(self::DEFAULT_TIMEZONE)))->getTimestamp();
        }
        catch (Exception $e)
        {
            throw new DateException($e->getMessage());
        }
    }

    /**
     * @throws DateException
     */
    public function format(string $date, string $format): string
    {
        try
        {
            return (new DateTime($date, new DateTimeZone(self::DEFAULT_TIMEZONE)))->format($format);
        }
        catch (Exception $e)
        {
            throw new DateException($e->getMessage());
        }
    }
}
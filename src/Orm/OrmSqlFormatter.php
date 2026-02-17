<?php declare(strict_types=1);

namespace Asterios\Core\Orm;

use Asterios\Core\Contracts\Orm\OrmSqlFormatterInterface;

class OrmSqlFormatter implements OrmSqlFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function backticks(string $value): string
    {
        if (stripos($value, ' AS ') !== false)
        {
            [$col, $alias] = preg_split('/\s+AS\s+/i', $value);

            return $this->backticks(trim($col)) . ' AS ' . $alias;
        }

        if (preg_match('/^(.+)\s+([a-zA-Z0-9_]+)$/', $value, $m))
        {
            $col = trim($m[1]);
            $alias = trim($m[2]);

            return $this->backticks($col) . ' AS ' . $alias;
        }

        if (str_contains($value, 'MD5'))
        {
            preg_match('/MD5\((.*?)\)/i', $value, $m);

            if (!empty($m[1]) && !str_contains($m[1], '`'))
            {
                return 'MD5(`' . $m[1] . '`)';
            }

            return $value;
        }

        if (str_contains($value, '.'))
        {
            preg_match('/([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)/', $value, $m);

            return '`' . $m[1] . '`.`' . $m[2] . '`';
        }

        return '`' . $value . '`';
    }

    /**
     * @inheritDoc
     */
    public function formatValue(string|int|float|null|bool $value): string
    {
        if (is_numeric($value))
        {
            return (string)$value;
        }

        if (is_bool($value))
        {
            return $value ? '"true"' : '"false"';
        }

        return '"' . $value . '"';
    }

    /**
     * @inheritDoc
     */
    public function formatInOperator(string $value): string
    {
        return '(' . $value . ')';
    }

    /**
     * @inheritDoc
     */
    public function isOperatorNull($operator): bool
    {
        return in_array($operator, ['IS NULL', 'IS NOT NULL'], true);
    }

    /**
     * @inheritDoc
     */
    public function open(): string
    {
        return '(';
    }

    /**
     * @inheritDoc
     */
    public function close(): string
    {
        return ')';
    }
}
<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Orm;

interface OrmSqlFormatterInterface
{
    /**
     * Transforms the input string by enclosing it in backticks.
     *
     * @param string $value The input string to be enclosed in backticks.
     * @return string The transformed string with backticks.
     */
    public function backticks(string $value): string;

    /**
     * @param string|int|float|null|bool $value
     * @return string
     */
    public function formatValue(string|int|float|null|bool $value): string;

    /**
     * @param string $value
     * @return string
     */
    public function formatInOperator(string $value): string;

    /**
     * @param $operator
     * @return bool
     */
    public function isOperatorNull($operator): bool;

    /**
     * @return string
     */
    public function open(): string;

    /**
     * @return string
     */
    public function close(): string;
}
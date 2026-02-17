<?php declare(strict_types=1);

namespace Asterios\Core\Enum\Orm;

use InvalidArgumentException;
use RuntimeException;

enum OperatorEnum: string
{
    case EQUAL = '=';
    case NOT_EQUAL = '!=';
    case GT = '>';
    case GT_OR_EQUAL = '>=';
    case LT = '<';
    case LT_OR_EQUAL = '<=';
    case BETWEEN = 'BETWEEN';
    case LIKE = 'LIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
    case IS_NULL = 'IS NULL';
    case IS_NOT_NULL = 'IS NOT NULL';

    public static function fromString(string $input): self
    {
        $normalized = self::normalize($input);

        return match ($normalized)
        {
            '=' => self::EQUAL,

            '!=', '<>' => self::NOT_EQUAL,

            '>' => self::GT,
            '>=' => self::GT_OR_EQUAL,
            '<' => self::LT,
            '<=' => self::LT_OR_EQUAL,

            'BETWEEN' => self::BETWEEN,
            'LIKE' => self::LIKE,

            'IN' => self::IN,
            'NOT IN' => self::NOT_IN,

            'IS NULL' => self::IS_NULL,
            'IS NOT NULL' => self::IS_NOT_NULL,

            default => throw new InvalidArgumentException(sprintf('Unknown operator "%s"', $input)),
        };
    }

    public static function isOperator(string|int|float|null $input): bool
    {
        return match ($input)
        {
            '=', '!=', '<>', '>', '>=', '<', '<=', 'BETWEEN', 'LIKE', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL' => true,

            default => false,
        };
    }

    /**
     * @param string $input
     * @return self|null
     */
    public static function tryFromString(string $input): ?self
    {
        try
        {
            return self::fromString($input);
        }
        catch (InvalidArgumentException)
        {
            return null;
        }
    }

    /**
     * @param string $value
     * @return string
     */
    private static function normalize(string $value): string
    {
        $value = trim($value);

        $value = preg_replace('/\s+/', ' ', $value);

        if ($value === null)
        {
            throw new RuntimeException('Regex normalization failed');
        }

        $value = preg_replace('/!\s+=/', '!=', $value);

        if ($value === null)
        {
            throw new RuntimeException('Regex normalization failed');
        }

        $value = preg_replace('/<\s+>/', '<>', $value);

        if ($value === null)
        {
            throw new RuntimeException('Regex normalization failed');
        }

        return strtoupper($value);
    }

    /**
     * @return bool
     */
    public function isUnary(): bool
    {
        return match ($this)
        {
            self::IS_NULL,
            self::IS_NOT_NULL => true,
            default => false,
        };
    }

    /**
     * @return bool
     */
    public function isRange(): bool
    {
        return $this === self::BETWEEN;
    }

    /**
     * @return bool
     */
    public function isSet(): bool
    {
        return in_array($this, [self::IN, self::NOT_IN], true);
    }
}

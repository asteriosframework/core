<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Interfaces\Stringable;

class Expression implements Stringable
{
    // Raw expression string
    protected $_value;

    /**
     * Sets the expression string.
     *
     *     $expression = new Db_Expression('COUNT(users.id)');
     *
     * @param string $value expression string
     */
    public function __construct(string $value)
    {
        // Set the expression string
        $this->_value = $value;
    }

    /**
     * Get the expression value as a string.
     *
     *     $sql = $expression->value();
     *
     * @return  string
     */
    public function value(): string
    {
        return $this->_value;
    }

    /**
     * Return the value of the expression as a string.
     *
     *     echo $expression;
     *
     * @return  string
     * @uses    Database_Expression::value
     */
    public function __toString(): string
    {
        return $this->value();
    }

}
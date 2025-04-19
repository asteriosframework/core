<?php declare(strict_types=1);

namespace Asterios\Core\Db\Migration;

class TableBluePrint
{
    protected string $table;
    protected array $columns = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id', bool $autoIncrement = true): void
    {
        $sqlAutoIncrement = (true === $autoIncrement) ? 'AUTO_INCREMENT' : '';
        $this->columns[] = '`' . $name . '` INT UNSIGNED ' . $sqlAutoIncrement . ' PRIMARY KEY';
    }

    public function string(string $name, int $length = 255): void
    {
        $this->columns[] = "`$name` VARCHAR($length) NOT NULL";
    }

    public function integer(string $name, bool $unsigned = true, bool $notNull = true): void
    {
        $type = $unsigned ? 'INT UNSIGNED' : 'INT';
        $this->columns[] = '`' . $name . '` ' . $type . $this->setNotNull($notNull);
    }

    public function int(string $name, bool $unsigned = true, bool $notNull = true): void
    {
        $this->integer($name, $unsigned, $notNull);
    }

    public function boolean(string $name, bool $notNull = true): void
    {
        $this->columns[] = '`' . $name . '` TINYINT(1)' . $this->setNotNull($notNull);
    }

    public function timestamps(): void
    {
        $this->columns[] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }

    protected function setNotNull(bool $setNull = true): string
    {
        return (true === $setNull) ? ' NOT NULL' : '';
    }

    public function toSql(): string
    {
        $columns = implode(",\n  ", $this->columns);

        return "CREATE TABLE IF NOT EXISTS `$this->table` (\n  $columns\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }
}

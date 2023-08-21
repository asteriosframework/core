<?php

declare(strict_types=1);

namespace Asterios\Test\Db\Connection;

use Asterios\Core\Db\Connection\MySqlConnection;
use Asterios\Core\Db\Exceptions\DbException;
use Asterios\Core\Db\Exceptions\DbQueryException;
use Asterios\Core\Db\ORM\Statement;
use Asterios\Core\Exception\DbConnectionManagerException;
use DateTime;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PDO;

class MySqlConnectionTest extends MockeryTestCase
{
    /**
     * @var MySqlConnection
     */
    protected MySqlConnection $testedClass;

    /**
     * @var array<string,string|int[]>
     */
    protected array $param = [];

    protected array $cleanup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->param = [
            'valid' => [
                'dsn' => "mysql:host=db;dbname=db;charset=utf8",
                'username' => 'db',
                'password' => 'db',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
            ],
            'invalid' => [
                'dsn' => "mysql:host=not-exists;dbname=db;charset=utf8",
                'username' => 'db',
                'password' => 'db',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
            ],
        ];

        $this->cleanup = [];
    }

    protected function tearDown(): void
    {
        if ([] === $this->cleanup) {
            return;
        }

        if (isset($this->cleanup['database'])) {
            $this->initTestedClass();

            foreach ($this->cleanup['database'] as $sql) {
                try {
                    $this->testedClass->exec($sql);
                } catch (\Throwable) {
                    // noop
                }
            }
        }
    }

    protected function initTestedClass(): MySqlConnection
    {
        $this->testedClass = new MySqlConnection(
            dsn: $this->param['valid']['dsn'],
            username: $this->param['valid']['username'],
            password: $this->param['valid']['password'],
            options: $this->param['valid']['options'],
        );

        return $this->testedClass;
    }

    public function test_connect_successfull(): void
    {
        $actual = new MySqlConnection(
            dsn: $this->param['valid']['dsn'],
            username: $this->param['valid']['username'],
            password: $this->param['valid']['password'],
            options: $this->param['valid']['options'],
        );

        self::assertInstanceOf(MySqlConnection::class, $actual);
    }

    public function test_connect_exception(): void
    {
        self::expectException(DbConnectionManagerException::class);

        $actual = new MySqlConnection(
            dsn: $this->param['invalid']['dsn'],
            username: $this->param['invalid']['username'],
            password: $this->param['invalid']['password'],
            options: $this->param['invalid']['options'],
        );
    }

    public function test_exec_exception(): void
    {
        self::expectException(DbException::class);

        $this->initTestedClass();

        $sql = 'CREATE Tble db_test( id int not null auto_increment primary key, surname varchar(10) not null) Enging=InnoDb;';

        $actual = $this->testedClass->exec($sql);
    }

    public function test_exec_successfull(): void
    {
        $timestamp = (new DateTime())->getTimestamp();
        $table = 'db_test_' . (string) $timestamp;

        if (!isset($this->cleanup['database'])) {
            $this->cleanup['database'] = [];
        }

        $this->cleanup['database'][] = 'DROP TABLE ' . $table;

        $this->initTestedClass();

        $sql = 'CREATE TABLE ' . $table . '( id int not null auto_increment primary key, surname varchar(10) not null) Engine=InnoDb;';

        $actual = $this->testedClass->exec($sql);

        self::assertNotFalse($actual);
        self::assertIsInt($actual);
        self::assertEquals(0, $actual);
    }

    public function test_error_info_code_message_driver_code(): void
    {
        $expectedMessage = "You have an error in your SQL syntax; check the manual that corresponds to your ";
        $expectedMessage .= "MariaDB server version for the right syntax to use near 'Tble db_test( id int not ";
        $expectedMessage .= "null auto_increment primary key, surname varchar(10)...' at line 1";

        $expectedInfo = [
            0 => '42000',
            1 => 1064,
            2 => $expectedMessage,
        ];

        $this->initTestedClass();

        $sql = 'CREATE Tble db_test( id int not null auto_increment primary key, surname varchar(10) not null) Enging=InnoDb;';

        try {
            $actual = $this->testedClass->exec($sql);
        } catch (\Throwable) {
            // noop
        }

        $errorCode = $this->testedClass->errorCode();
        $errorInfo = $this->testedClass->errorInfo();
        $errorDriverCode = $this->testedClass->errorDriverCode();
        $errorMessage = $this->testedClass->errorMessage();

        self::assertEquals('42000', $errorCode);
        self::assertEquals($expectedInfo, $errorInfo);
        self::assertEquals(1064, $errorDriverCode);
        self::assertEquals($expectedMessage, $errorMessage);
    }

    public function test_query_exception(): void
    {
        $this->initTestedClass();

        self::expectException(DbQueryException::class);

        $actual = $this->testedClass->query('SELECT * FROM db_test_not_exists');
    }

    public function test_query_successfull(): void
    {
        $timestamp = (new DateTime())->getTimestamp();
        $table = 'db_test_' . (string) $timestamp;

        $create = 'CREATE TABLE ' . $table . '( id int not null auto_increment primary key, surname varchar(10) not null) Engine=InnoDb;';
        $insert = 'INSERT INTO ' . $table . '(surname) VALUES (\'Hendrix\');';

        if (!isset($this->cleanup['database'])) {
            $this->cleanup['database'] = [];
        }

        $this->cleanup['database'][] = 'DROP TABLE ' . $table;

        $this->initTestedClass();

        $this->testedClass->exec($create);
        $this->testedClass->exec($insert);

        $actual = $this->testedClass->query('SELECT id, surname FROM ' . $table);

        self::assertInstanceOf(Statement::class, $actual);

        $result = $actual->fetchAll(Statement::FETCH_DEFAULT);
        self::assertEquals([(object) ['id' => 1, 'surname' => 'Hendrix']], $result);
    }

    public function test_query_not_found(): void
    {
        $timestamp = (new DateTime())->getTimestamp();
        $table = 'db_test_' . (string) $timestamp;
        $create = 'CREATE TABLE ' . $table . '( id int not null auto_increment primary key, surname varchar(10) not null) Engine=InnoDb;';

        if (!isset($this->cleanup['database'])) {
            $this->cleanup['database'] = [];
        }

        $this->cleanup['database'][] = 'DROP TABLE ' . $table;

        $this->initTestedClass();

        $this->testedClass->exec($create);

        $actual = $this->testedClass->query('SELECT id, surname FROM ' . $table);

        self::assertTrue(count($actual->fetchAll(Statement::FETCH_DEFAULT)) === 0);
    }

}
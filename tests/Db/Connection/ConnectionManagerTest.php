<?php

declare(strict_types=1);

namespace Asterios\Test\Db\Connection;

use Asterios\Core\Config;
use Asterios\Core\Asterios;
use Asterios\Core\Db\Connection\MySqlConnection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Asterios\Core\Db\Connection\ConnectionManager;

class ConnectionManagerTest extends MockeryTestCase
{
    protected string $config_path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config_path = implode(
            DIRECTORY_SEPARATOR,
            [
                __DIR__,
                '/../../../',
                'tests',
                'testdata',
                'Db',
                'config'
            ]
        );

        Asterios::setEnvironment(Asterios::DEVELOPMENT);
        Config::set_config_path($this->config_path);
    }

    public function test_can_connect_to_database(): void
    {
        $actual = ConnectionManager::create();

        self::assertEquals(Config::get_memory('DbConnection'), $actual->getConnection());
        self::assertInstanceOf(MySqlConnection::class, $actual->getConnection());
    }
}
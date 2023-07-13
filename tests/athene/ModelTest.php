<?php declare(strict_types=1);

namespace Asterios\Test\athene;

use Asterios\Core\Athene\Model;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\ORMSetup;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;


class ModelTest extends MockeryTestCase
{
    /**
     * @var Configuration
     */
    protected Configuration $config;
    /**
     * @var Connection
     */
    protected Connection $conn;
    /**
     * @var EntityManager
     */
    protected EntityManager $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/TestData/Models'],
            isDevMode: true
        );

        $connectionParams = [
            'driver' => 'sqlite3',
            'memory' => true,
        ];

        $this->conn = DriverManager::getConnection($connectionParams);
        $this->entityManager = new EntityManager($this->conn, $this->config);
    }
}
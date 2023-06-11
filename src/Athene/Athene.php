<?php

declare(strict_types=1);

namespace Asterios\Core\Athene;

use Asterios\Core\Config;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;

class Athene
{
    protected string $config_file = __DIR__ . '/../config/athene.php';
    protected array $config = [];
    protected EntityManager $entityManager;

    public function __construct(
        protected Configuration $configuration,
        protected Connection $connection
    ) {
        $this->config = Config::load($this->config_file);

        $this->configuration = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/../Models'],
            isDevMode: true,
        );

        $this->connection = DriverManager::getConnection([
            'dbname' => $this->config['dbname'],
            'user' => $this->config['user'],
            'password' => $this->config['password'],
            'host' => $this->config['host'],
            'driver' => $this->config['driver'],
        ]);

        $this->entityManager = new EntityManager($this->connection, $this->configuration);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
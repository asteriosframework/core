<?php

declare(strict_types=1);

namespace Asterios\Core\Athene;

use Asterios\Core\Config;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;

// use Doctrine\ORM\Tools\Console\ConsoleRunner;
// use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;


class Athene
{
    protected array $config = [];
    protected EntityManager $entityManager;

    public function __construct(
        protected Configuration|null $configuration = null,
        protected Connection|null $connection = null
    ) {
        $this->config = Config::load('athene');

        if (null === $configuration) {
            $this->configuration = ORMSetup::createAttributeMetadataConfiguration(
                paths: [__DIR__ . '/../Models'],
                isDevMode: true,
            );
        }

        if (null === $configuration) {
            $this->connection = DriverManager::getConnection([
                'dbname' => $this->config['dbname'],
                'user' => $this->config['user'],
                'password' => $this->config['password'],
                'host' => $this->config['host'],
                'driver' => $this->config['driver'],
            ]);
        }

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
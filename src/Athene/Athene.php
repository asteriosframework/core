<?php

declare(strict_types=1);

namespace Asterios\Core\Athene;

use Asterios\Core\Config;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;

class Athene
{
    protected array $config = [];
    protected EntityManager $entityManager;

    protected DependencyFactory $dependencyFactory;

    public function __construct(
        protected Configuration|null $configuration = null,
        protected Connection|null $connection = null
    ) {
        $this->boot($configuration, $connection);
    }

    public function boot(Configuration $configuration = null, Connection $connection = null): void
    {
        $this->config = Config::load('athene');

        if (null === $configuration)
        {
            $this->configuration = ORMSetup::createAttributeMetadataConfiguration(
                paths: [$this->config['athene']['model_path']],
                isDevMode: $this->config['athene']['is_dev_mode'],
            );
        }

        if (null === $configuration)
        {
            $this->connection = DriverManager::getConnection([
                'dbname' => $this->config['athene']['connections']['default']['dbname'],
                'user' => $this->config['athene']['connections']['default']['user'],
                'password' => $this->config['athene']['connections']['default']['password'],
                'host' => $this->config['athene']['connections']['default']['host'],
                'driver' => $this->config['athene']['connections']['default']['driver'],
            ]);
        }

        $this->entityManager = new EntityManager($this->connection, $this->configuration);

        $this->dependencyFactory = DependencyFactory::fromEntityManager(
            new ConfigurationArray(
                $this->config['athene']['migrations']
            ), new ExistingEntityManager($this->entityManager));
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function getDependencyFactory(): DependencyFactory
    {
        return $this->dependencyFactory;
    }
}
<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class AbstractBehaviorTestCase extends TestCase
{
    protected EntityManagerInterface $entityManager;

    private ContainerInterface $container;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/config/config_test.php';

        $this->container = $container();

        $this->entityManager = $this->getService(EntityManagerInterface::class);
        $this->loadDatabaseFixtures();
    }

    protected function loadDatabaseFixtures(): void
    {
        /** @var DatabaseLoader $databaseLoader */
        $databaseLoader = $this->getService(DatabaseLoader::class);
        $databaseLoader->reload();
    }

    protected function isPostgreSql(): bool
    {
        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        return $connection->getDatabasePlatform() instanceof PostgreSQL94Platform;
    }

    /**
     * @return string[]
     */
    protected function provideCustomConfigs(): array
    {
        return [];
    }

    protected function createAndRegisterDebugStack(): DebugStack
    {
        $debugStack = new DebugStack();

        $this->entityManager->getConnection()
            ->getConfiguration()
            ->setSQLLogger($debugStack);

        return $debugStack;
    }

    /**
     * @template T as object
     * @param class-string<T> $type
     * @return T
     */
    protected function getService(string $type): object
    {
        return $this->container->get($type);
    }
}

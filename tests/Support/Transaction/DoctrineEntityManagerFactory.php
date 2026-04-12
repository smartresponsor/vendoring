<?php

declare(strict_types=1);

namespace App\Tests\Support\Transaction;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

final class DoctrineEntityManagerFactory
{
    public static function createSqliteMemoryEntityManager(string $projectRoot): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [$projectRoot . '/src/Entity'],
            isDevMode: true,
        );
        $config->enableNativeLazyObjects(true);

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        return new EntityManager($connection, $config);
    }
}

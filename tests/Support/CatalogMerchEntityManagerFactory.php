<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support;

use App\Vendoring\Entity\VendorCatalogCategoryBanner;
use App\Vendoring\Entity\VendorCatalogCategoryHtmlBlock;
use App\Vendoring\Entity\VendorCatalogCategoryPin;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

final class CatalogMerchEntityManagerFactory
{
    public static function createSqliteMemoryEntityManager(string $projectRoot): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfig(
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

    /**
     * @return list<class-string>
     */
    public static function catalogMerchClasses(): array
    {
        return [
            VendorCatalogCategoryBanner::class,
            VendorCatalogCategoryHtmlBlock::class,
            VendorCatalogCategoryPin::class,
        ];
    }
}

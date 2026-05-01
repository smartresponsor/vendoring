<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryBannerEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryHtmlBlockEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryPinEntity;
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
            VendorCatalogCategoryBannerEntity::class,
            VendorCatalogCategoryHtmlBlockEntity::class,
            VendorCatalogCategoryPinEntity::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryBannerEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryHtmlBlockEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryPinEntity;
use App\Vendoring\Service\Catalog\VendorCatalogMerchService;
use App\Vendoring\Tests\Support\CatalogMerchEntityManagerFactory;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

final class CatalogMerchServiceTest extends TestCase
{
    public function testPinCreateOrderSetAndDeleteUseOrmState(): void
    {
        $entityManager = CatalogMerchEntityManagerFactory::createSqliteMemoryEntityManager(dirname(__DIR__, 3));
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema(array_map(
            static fn(string $class): \Doctrine\ORM\Mapping\ClassMetadata => $entityManager->getClassMetadata($class),
            CatalogMerchEntityManagerFactory::catalogMerchClasses(),
        ));

        $service = new VendorCatalogMerchService($entityManager);
        $service->pinCreate('cat-1', 'record-a', 10);
        $service->pinCreate('cat-1', 'record-b', 20);
        $service->orderSet('cat-1', ['record-b', 'record-a']);

        /** @var VendorCatalogCategoryPinEntity|null $first */
        $first = $entityManager->getRepository(VendorCatalogCategoryPinEntity::class)->findOneBy([
            'categoryId' => 'cat-1',
            'recordId' => 'record-a',
        ]);
        /** @var VendorCatalogCategoryPinEntity|null $second */
        $second = $entityManager->getRepository(VendorCatalogCategoryPinEntity::class)->findOneBy([
            'categoryId' => 'cat-1',
            'recordId' => 'record-b',
        ]);

        self::assertNotNull($first);
        self::assertNotNull($second);
        self::assertSame(1, $first->position());
        self::assertSame(0, $second->position());

        $service->pinDelete('cat-1', 'record-a');
        $deleted = $entityManager->getRepository(VendorCatalogCategoryPinEntity::class)->findOneBy([
            'categoryId' => 'cat-1',
            'recordId' => 'record-a',
        ]);

        self::assertNull($deleted);
    }

    public function testBannerAndHtmlPublishPersistPublishedState(): void
    {
        $entityManager = CatalogMerchEntityManagerFactory::createSqliteMemoryEntityManager(dirname(__DIR__, 3));
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema(array_map(
            static fn(string $class): \Doctrine\ORM\Mapping\ClassMetadata => $entityManager->getClassMetadata($class),
            CatalogMerchEntityManagerFactory::catalogMerchClasses(),
        ));

        $service = new VendorCatalogMerchService($entityManager);
        $bannerId = $service->bannerPublish('cat-2', 'Top banner', 'Banner content');
        $htmlId = $service->htmlPublish('cat-2', '<p>Hello</p>');

        /** @var VendorCatalogCategoryBannerEntity|null $banner */
        $banner = $entityManager->find(VendorCatalogCategoryBannerEntity::class, $bannerId);
        /** @var VendorCatalogCategoryHtmlBlockEntity|null $htmlBlock */
        $htmlBlock = $entityManager->find(VendorCatalogCategoryHtmlBlockEntity::class, $htmlId);

        self::assertNotNull($banner);
        self::assertNotNull($htmlBlock);
        self::assertSame('cat-2', $banner->categoryId());
        self::assertSame('Top banner', $banner->title());
        self::assertSame('Banner content', $banner->content());
        self::assertTrue($banner->published());
        self::assertSame('cat-2', $htmlBlock->categoryId());
        self::assertSame('<p>Hello</p>', $htmlBlock->html());
        self::assertTrue($htmlBlock->published());
    }
}

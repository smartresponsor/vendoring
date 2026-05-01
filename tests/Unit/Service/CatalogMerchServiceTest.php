<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Entity\VendorCatalogCategoryBanner;
use App\Vendoring\Entity\VendorCatalogCategoryHtmlBlock;
use App\Vendoring\Entity\VendorCatalogCategoryPin;
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

        /** @var VendorCatalogCategoryPin|null $first */
        $first = $entityManager->getRepository(VendorCatalogCategoryPin::class)->findOneBy([
            'categoryId' => 'cat-1',
            'recordId' => 'record-a',
        ]);
        /** @var VendorCatalogCategoryPin|null $second */
        $second = $entityManager->getRepository(VendorCatalogCategoryPin::class)->findOneBy([
            'categoryId' => 'cat-1',
            'recordId' => 'record-b',
        ]);

        self::assertNotNull($first);
        self::assertNotNull($second);
        self::assertSame(1, $first->position());
        self::assertSame(0, $second->position());

        $service->pinDelete('cat-1', 'record-a');
        $deleted = $entityManager->getRepository(VendorCatalogCategoryPin::class)->findOneBy([
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

        /** @var VendorCatalogCategoryBanner|null $banner */
        $banner = $entityManager->find(VendorCatalogCategoryBanner::class, $bannerId);
        /** @var VendorCatalogCategoryHtmlBlock|null $htmlBlock */
        $htmlBlock = $entityManager->find(VendorCatalogCategoryHtmlBlock::class, $htmlId);

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

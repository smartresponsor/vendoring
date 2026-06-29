<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Catalog;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryBannerEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryHtmlBlockEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryPinEntity;
use App\Vendoring\ServiceInterface\Catalog\VendorCatalogMerchServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class VendorCatalogMerchService implements VendorCatalogMerchServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function pinCreate(string $categoryId, string $recordId, int $position): void
    {
        $pin = new VendorCatalogCategoryPinEntity($categoryId, $recordId, $position);
        $this->entityManager->persist($pin);
        $this->entityManager->flush();
    }

    public function pinDelete(string $categoryId, string $recordId): void
    {
        $pin = $this->findPin($categoryId, $recordId);

        if (null === $pin) {
            return;
        }

        $this->entityManager->remove($pin);
        $this->entityManager->flush();
    }

    /**
     * @param list<string> $recordIds
     */
    public function orderSet(string $categoryId, array $recordIds): void
    {
        $position = 0;
        foreach ($recordIds as $recordId) {
            $pin = $this->findPin($categoryId, $recordId);

            if (null === $pin) {
                ++$position;
                continue;
            }

            $pin->reorder($position);
            ++$position;
        }

        $this->entityManager->flush();
    }

    public function bannerPublish(string $categoryId, string $title, string $content): string
    {
        $banner = new VendorCatalogCategoryBannerEntity($categoryId, $title, $content);
        $banner->publish();
        $this->entityManager->persist($banner);
        $this->entityManager->flush();

        return $banner->id();
    }

    public function htmlPublish(string $categoryId, string $html): string
    {
        $htmlBlock = new VendorCatalogCategoryHtmlBlockEntity($categoryId, $html);
        $htmlBlock->publish();
        $this->entityManager->persist($htmlBlock);
        $this->entityManager->flush();

        return $htmlBlock->id();
    }

    private function findPin(string $categoryId, string $recordId): ?VendorCatalogCategoryPinEntity
    {
        // ObjectCodeEmbeddableTrait stores categoryId as object_code via embedded
        /** @var VendorCatalogCategoryPinEntity|null $pin */
        $pin = $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(VendorCatalogCategoryPinEntity::class, 'p')
            ->where('p.objectCode.objectCode = :categoryId')
            ->andWhere('p.recordId = :recordId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('recordId', $recordId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $pin;
    }
}

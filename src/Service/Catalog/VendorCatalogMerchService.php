<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Catalog;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryBannerEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryHtmlBlockEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryPinEntity;
use App\Vendoring\ServiceInterface\Catalog\VendorCatalogMerchServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class VendorCatalogMerchService implements VendorCatalogMerchServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function pinCreate(string $categoryId, string $recordId, int $position): void
    {
        $pin = new VendorCatalogCategoryPinEntity($categoryId, $recordId, $position);
        $this->entityManager->persist($pin);
        $this->entityManager->flush();
    }

    public function pinDelete(string $categoryId, string $recordId): void
    {
        $pin = $this->entityManager->getRepository(VendorCatalogCategoryPinEntity::class)->findOneBy([
            'categoryId' => $categoryId,
            'recordId' => $recordId,
        ]);

        if (null === $pin) {
            return;
        }

        $this->entityManager->remove($pin);
        $this->entityManager->flush();
    }

    /**
     * @param string $categoryId
     * @param list<string> $recordIds
     */
    public function orderSet(string $categoryId, array $recordIds): void
    {
        $position = 0;
        $repository = $this->entityManager->getRepository(VendorCatalogCategoryPinEntity::class);
        foreach ($recordIds as $recordId) {
            $pin = $repository->findOneBy([
                'categoryId' => $categoryId,
                'recordId' => $recordId,
            ]);

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
}

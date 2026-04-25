<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Entity\VendorCatalogCategoryBanner;
use App\Vendoring\Entity\VendorCatalogCategoryHtmlBlock;
use App\Vendoring\Entity\VendorCatalogCategoryPin;
use App\Vendoring\ServiceInterface\CatalogMerchServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CatalogMerchService implements CatalogMerchServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function pinCreate(string $categoryId, string $recordId, int $position): void
    {
        $pin = new VendorCatalogCategoryPin($categoryId, $recordId, $position);
        $this->entityManager->persist($pin);
        $this->entityManager->flush();
    }

    public function pinDelete(string $categoryId, string $recordId): void
    {
        $pin = $this->entityManager->getRepository(VendorCatalogCategoryPin::class)->findOneBy([
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
        $repository = $this->entityManager->getRepository(VendorCatalogCategoryPin::class);
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
        $banner = new VendorCatalogCategoryBanner($categoryId, $title, $content);
        $banner->publish();
        $this->entityManager->persist($banner);
        $this->entityManager->flush();

        return $banner->id();
    }

    public function htmlPublish(string $categoryId, string $html): string
    {
        $htmlBlock = new VendorCatalogCategoryHtmlBlock($categoryId, $html);
        $htmlBlock->publish();
        $this->entityManager->persist($htmlBlock);
        $this->entityManager->flush();

        return $htmlBlock->id();
    }
}

<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Catalog;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryBannerEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryHtmlBlockEntity;
use App\Vendoring\Entity\Vendor\VendorCatalogCategoryPinEntity;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryBannerRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryHtmlBlockRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryPinRepositoryInterface;
use App\Vendoring\ServiceInterface\Catalog\VendorCatalogMerchServiceInterface;

final readonly class VendorCatalogMerchService implements VendorCatalogMerchServiceInterface
{
    public function __construct(
        private VendorCatalogCategoryPinRepositoryInterface $pinRepository,
        private VendorCatalogCategoryBannerRepositoryInterface $bannerRepository,
        private VendorCatalogCategoryHtmlBlockRepositoryInterface $htmlBlockRepository,
    ) {
    }

    public function pinCreate(string $categoryId, string $recordId, int $position): void
    {
        $pin = new VendorCatalogCategoryPinEntity($categoryId, $recordId, $position);
        $this->pinRepository->save($pin, true);
    }

    public function pinDelete(string $categoryId, string $recordId): void
    {
        $pin = $this->findPin($categoryId, $recordId);

        if (null === $pin) {
            return;
        }

        $this->pinRepository->remove($pin, true);
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

        $this->pinRepository->flush();
    }

    public function bannerPublish(string $categoryId, string $title, string $content): string
    {
        $banner = new VendorCatalogCategoryBannerEntity($categoryId, $title, $content);
        $banner->publish();
        $this->bannerRepository->save($banner, true);

        return $banner->id();
    }

    public function htmlPublish(string $categoryId, string $html): string
    {
        $htmlBlock = new VendorCatalogCategoryHtmlBlockEntity($categoryId, $html);
        $htmlBlock->publish();
        $this->htmlBlockRepository->save($htmlBlock, true);

        return $htmlBlock->id();
    }

    private function findPin(string $categoryId, string $recordId): ?VendorCatalogCategoryPinEntity
    {
        $pin = $this->pinRepository->findOneByCategoryAndRecord($categoryId, $recordId);

        return $pin instanceof VendorCatalogCategoryPinEntity ? $pin : null;
    }
}

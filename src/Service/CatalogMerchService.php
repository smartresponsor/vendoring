<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service;

use App\Entity\CategoryBanner;
use App\Entity\CategoryHtmlBlock;
use App\Entity\CategoryPin;
use App\ServiceInterface\CatalogMerchServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Application service for catalog merch operations.
 */
final class CatalogMerchService implements CatalogMerchServiceInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Executes the pin create operation for this runtime surface.
     */
    public function pinCreate(string $categoryId, string $recordId, int $position): void
    {
        $pin = new CategoryPin($categoryId, $recordId, $position);
        $this->entityManager->persist($pin);
        $this->entityManager->flush();
    }

    /**
     * Executes the pin delete operation for this runtime surface.
     */
    public function pinDelete(string $categoryId, string $recordId): void
    {
        $pin = $this->entityManager->getRepository(CategoryPin::class)->findOneBy([
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
     * @param list<string> $recordIds
     */
    public function orderSet(string $categoryId, array $recordIds): void
    {
        $position = 0;
        foreach ($recordIds as $recordId) {
            $this->entityManager->getConnection()->executeStatement(
                'UPDATE category_pin SET position = ? WHERE category_id = ? AND record_id = ?',
                [$position++, $categoryId, $recordId],
            );
        }
    }

    /**
     * Executes the banner publish operation for this runtime surface.
     */
    public function bannerPublish(string $categoryId, string $title, string $content): string
    {
        $banner = new CategoryBanner($categoryId, $title, $content);
        $banner->publish();
        $this->entityManager->persist($banner);
        $this->entityManager->flush();

        return (string) $banner->id();
    }

    /**
     * Executes the html publish operation for this runtime surface.
     */
    public function htmlPublish(string $categoryId, string $html): string
    {
        $htmlBlock = new CategoryHtmlBlock($categoryId, $html);
        $htmlBlock->publish();
        $this->entityManager->persist($htmlBlock);
        $this->entityManager->flush();

        return (string) $htmlBlock->id();
    }
}

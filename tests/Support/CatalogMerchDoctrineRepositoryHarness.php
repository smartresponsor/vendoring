<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryPinEntity;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryBannerRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryHtmlBlockRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryPinRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CatalogMerchDoctrineRepositoryHarness implements VendorCatalogCategoryPinRepositoryInterface, VendorCatalogCategoryBannerRepositoryInterface, VendorCatalogCategoryHtmlBlockRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function find(mixed $id): ?object
    {
        return null;
    }

    public function byId(mixed $id): ?object
    {
        return null;
    }

    public function findOneBy(array $criteria): ?object
    {
        return null;
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->entityManager->persist($entity);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(object $entity, bool $flush = false): void
    {
        $this->entityManager->remove($entity);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function findOneByCategoryAndRecord(string $categoryId, string $recordId): ?object
    {
        foreach ($this->entityManager->getRepository(VendorCatalogCategoryPinEntity::class)->findAll() as $pin) {
            if ($pin instanceof VendorCatalogCategoryPinEntity
                && $pin->categoryId() === $categoryId
                && $pin->recordId() === $recordId) {
                return $pin;
            }
        }

        return null;
    }
}

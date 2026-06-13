<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

interface VendorProfileCoverRepositoryInterface
{
    public function find(mixed $id): ?object;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?object;

    /** @return list<object> */
    public function findBy(array $criteria): array;

    public function save(object $entity, bool $flush = false): void;
}

<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface;

interface VendorDocumentAttachmentRepositoryInterface
{
    public function find(mixed $id): ?object;

    public function byId(mixed $id): ?object;

    /** @param array<string,mixed> $criteria */
    public function findOneBy(array $criteria): ?object;

    /**
     * @param array<string,mixed>       $criteria
     * @param array<string,string>|null $orderBy
     *
     * @return list<object>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function save(object $entity, bool $flush = false): void;
}

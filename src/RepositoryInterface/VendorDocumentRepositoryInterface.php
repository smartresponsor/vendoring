<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorDocument;

interface VendorDocumentRepositoryInterface
{
    public function find(int $id): ?VendorDocument;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorDocument;

    /**
     * @return list<VendorDocument>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}

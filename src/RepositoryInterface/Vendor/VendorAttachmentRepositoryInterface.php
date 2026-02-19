<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorAttachment;

interface VendorAttachmentRepositoryInterface
{
    public function find(int $id): ?VendorAttachment;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorAttachment;

    /**
     * @return list<VendorAttachment>
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
}

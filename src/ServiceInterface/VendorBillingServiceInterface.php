<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\VendorBillingDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorBilling;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorBillingServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsert(Vendor $vendor, VendorBillingDTO $dto): VendorBilling;

    /** @throws ORMException|OptimisticLockException */
    public function requestPayout(VendorBilling $billing, int $amountMinor): void;

    /** @throws ORMException|OptimisticLockException */
    public function completePayout(VendorBilling $billing, int $amountMinor): void;
}

<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Billing;

use App\Vendoring\DTO\VendorBillingDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface VendorBillingServiceInterface
{
    /** @throws ORMException|OptimisticLockException */
    public function upsert(VendorEntity $vendor, VendorBillingDTO $dto): VendorBillingEntity;

    /** @throws ORMException|OptimisticLockException */
    public function requestPayout(VendorBillingEntity $billing, int $amountMinor): void;

    /** @throws ORMException|OptimisticLockException */
    public function completePayout(VendorBillingEntity $billing, int $amountMinor): void;
}

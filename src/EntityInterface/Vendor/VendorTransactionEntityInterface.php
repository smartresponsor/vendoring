<?php

declare(strict_types=1);

namespace App\Vendoring\EntityInterface\Vendor;

interface VendorTransactionEntityInterface
{
    public function getVendorId(): string;

    public function getOrderId(): string;

    public function getStatus(): string;
}

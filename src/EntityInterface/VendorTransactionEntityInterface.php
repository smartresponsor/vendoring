<?php

declare(strict_types=1);

namespace App\Vendoring\EntityInterface;

interface VendorTransactionEntityInterface
{
    public function getId(): ?int;

    public function getVendorId(): string;

    public function getOrderId(): string;

    public function getStatus(): string;
}

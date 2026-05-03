<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Transaction;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\ServiceInterface\Transaction\VendorTransactionLifecycleServiceInterface;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;

final class FakeVendorTransactionLifecycle implements VendorTransactionLifecycleServiceInterface
{
    public ?VendorTransactionEntityEntity $updatedTransaction = null;
    public ?string $updatedStatus = null;
    public ?VendorTransactionEntityDataValueObject $createdData = null;
    public ?\InvalidArgumentException $exceptionToThrow = null;

    public function __construct(private readonly VendorTransactionEntity $transaction) {}

    public function createTransaction(VendorTransactionDataValueObject $data): VendorTransaction
    {
        if ($this->exceptionToThrow instanceof \InvalidArgumentException) {
            throw $this->exceptionToThrow;
        }

        $this->createdData = $data;

        return $this->transaction;
    }

    public function updateStatus(VendorTransactionEntity $tx, string $status): VendorTransaction
    {
        if ($this->exceptionToThrow instanceof \InvalidArgumentException) {
            throw $this->exceptionToThrow;
        }

        $this->updatedTransaction = $tx;
        $this->updatedStatus = $status;
        $tx->setStatus($status);

        return $tx;
    }
}

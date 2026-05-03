<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Transaction;

use App\Vendoring\Entity\VendorTransaction;
use App\Vendoring\ServiceInterface\Transaction\VendorTransactionLifecycleServiceInterface;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;

final class FakeVendorTransactionLifecycle implements VendorTransactionLifecycleServiceInterface
{
    public ?VendorTransaction $updatedTransaction = null;
    public ?string $updatedStatus = null;
    public ?VendorTransactionDataValueObject $createdData = null;
    public ?\InvalidArgumentException $exceptionToThrow = null;

    public function __construct(private readonly VendorTransaction $transaction) {}

    public function createTransaction(VendorTransactionDataValueObject $data): VendorTransaction
    {
        if ($this->exceptionToThrow instanceof \InvalidArgumentException) {
            throw $this->exceptionToThrow;
        }

        $this->createdData = $data;

        return $this->transaction;
    }

    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction
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

<?php

declare(strict_types=1);

namespace App\Tests\Support\Transaction;

use App\Entity\Vendor\VendorTransaction;
use App\ServiceInterface\VendorTransactionManagerInterface;
use App\ValueObject\VendorTransactionData;

final class FakeVendorTransactionManager implements VendorTransactionManagerInterface
{
    public ?VendorTransaction $updatedTransaction = null;
    public ?string $updatedStatus = null;
    public ?VendorTransactionData $createdData = null;
    public ?\InvalidArgumentException $exceptionToThrow = null;

    public function __construct(private readonly VendorTransaction $transaction)
    {
    }

    public function createTransaction(VendorTransactionData $data): VendorTransaction
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

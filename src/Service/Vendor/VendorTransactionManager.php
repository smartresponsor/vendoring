<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service\Vendor;

use App\Entity\Vendor\VendorTransaction;
use App\Event\Vendor\VendorTransactionEvent;
use App\ServiceInterface\Vendor\VendorTransactionManagerInterface;
use App\ValueObject\Vendor\VendorTransactionData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorTransactionManager implements VendorTransactionManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly EventDispatcherInterface $dispatcher
    )
    {
    }

    public function createTransaction(VendorTransactionData $data): VendorTransaction
    {
        $tx = new VendorTransaction(
            vendorId: $data->vendorId,
            orderId: $data->orderId,
            projectId: $data->projectId,
            amount: $data->amount
        );

        $this->em->persist($tx);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorTransactionEvent($tx), VendorTransactionEvent::NAME);

        return $tx;
    }

    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction
    {
        $tx->setStatus($status);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorTransactionEvent($tx), VendorTransactionEvent::NAME);

        return $tx;
    }
}

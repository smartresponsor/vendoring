<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Controller\Vendor;

use App\Entity\Vendor\VendorTransaction;
use App\Service\Vendor\VendorTransactionManager;
use App\ValueObject\Vendor\VendorTransactionData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorTransactionController
{
    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly VendorTransactionManager $manager
    )
    {
    }

    public function create(Request $request): JsonResponse
    {
        $payload = $request->toArray();

        $data = new VendorTransactionData(
            vendorId: (string)($payload['vendorId'] ?? ''),
            orderId: (string)($payload['orderId'] ?? ''),
            projectId: isset($payload['projectId']) ? (string)$payload['projectId'] : null,
            amount: (string)($payload['amount'] ?? '0.00')
        );

        $tx = $this->manager->createTransaction($data);

        return new JsonResponse(['id' => $tx->getId(), 'status' => $tx->getStatus()], 201);
    }

    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $tx = $this->em->find(VendorTransaction::class, $id);
        if (!$tx instanceof VendorTransaction) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        $payload = $request->toArray();
        $status = (string)($payload['status'] ?? $tx->getStatus());

        $tx = $this->manager->updateStatus($tx, $status);

        return new JsonResponse(['id' => $tx->getId(), 'status' => $tx->getStatus()]);
    }
}

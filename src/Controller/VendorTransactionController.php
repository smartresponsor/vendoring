<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Controller;

use App\Entity\Vendor\VendorTransaction;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\ServiceInterface\VendorTransactionManagerInterface;
use App\ValueObject\VendorTransactionData;
use App\ValueObject\VendorTransactionErrorCode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-transactions')]
final class VendorTransactionController extends AbstractController
{
    public function __construct(
        private readonly VendorTransactionRepositoryInterface $repo,
        private readonly VendorTransactionManagerInterface $manager,
    ) {
    }

    /**
     * Create a vendor transaction from a JSON payload.
     *
     * Request schema: vendorId:string, orderId:string, projectId:?string, amount:string(decimal).
     * Success response: {id:int,status:string}.
     * Error responses: malformed_json(400), duplicate_transaction(409), validation errors(422).
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return new JsonResponse(['error' => VendorTransactionErrorCode::MALFORMED_JSON], 400);
        }

        foreach ([
            'vendorId' => VendorTransactionErrorCode::VENDOR_ID_REQUIRED,
            'orderId' => VendorTransactionErrorCode::ORDER_ID_REQUIRED,
            'amount' => VendorTransactionErrorCode::AMOUNT_REQUIRED,
        ] as $field => $errorCode) {
            if (!isset($payload[$field]) || '' === (string) $payload[$field]) {
                return new JsonResponse(['error' => $errorCode], 422);
            }
        }

        $projectId = null;
        if (array_key_exists('projectId', $payload) && null !== $payload['projectId']) {
            $normalizedProjectId = trim((string) $payload['projectId']);
            $projectId = '' === $normalizedProjectId ? null : $normalizedProjectId;
        }

        $data = new VendorTransactionData(
            vendorId: trim((string) $payload['vendorId']),
            orderId: trim((string) $payload['orderId']),
            projectId: $projectId,
            amount: (string) $payload['amount'],
        );

        try {
            $tx = $this->manager->createTransaction($data);
        } catch (\InvalidArgumentException $exception) {
            $errorCode = $this->normalizeErrorCode($exception->getMessage());
            $statusCode = VendorTransactionErrorCode::DUPLICATE_TRANSACTION === $errorCode ? 409 : 422;

            return new JsonResponse(['error' => $errorCode], $statusCode);
        }

        return new JsonResponse(['id' => $tx->getId(), 'status' => $tx->getStatus()], 201);
    }

    /**
     * List all transactions for a single vendor.
     *
     * Response schema: {data: VendorTransactionResource[]}.
     */
    #[Route('/vendor/{vendorId}', methods: ['GET'])]
    public function listByVendor(string $vendorId): JsonResponse
    {
        $items = [];
        foreach ($this->repo->findByVendorId($vendorId) as $tx) {
            $items[] = $this->normalize($tx);
        }

        return new JsonResponse(['data' => $items], 200);
    }

    /**
     * Update the status of a vendor transaction.
     *
     * Request schema: {status:string}.
     * Success response: {id:int,status:string}.
     * Error responses: malformed_json(400), not_found(404), validation errors(422).
     */
    #[Route('/vendor/{vendorId}/{id}/status', methods: ['POST'])]
    public function updateStatus(string $vendorId, int $id, Request $request): JsonResponse
    {
        $transaction = $this->repo->findOneByIdAndVendorId($id, $vendorId);

        if (!$transaction instanceof VendorTransaction) {
            return new JsonResponse(['error' => VendorTransactionErrorCode::NOT_FOUND], 404);
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return new JsonResponse(['error' => VendorTransactionErrorCode::MALFORMED_JSON], 400);
        }

        $status = isset($payload['status']) ? (string) $payload['status'] : '';

        if ('' === $status) {
            return new JsonResponse(['error' => VendorTransactionErrorCode::STATUS_REQUIRED], 422);
        }

        try {
            $updated = $this->manager->updateStatus($transaction, $status);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $this->normalizeErrorCode($exception->getMessage())], 422);
        }

        return new JsonResponse(['id' => $updated->getId(), 'status' => $updated->getStatus()]);
    }

    private function normalizeErrorCode(string $message): string
    {
        return match ($message) {
            VendorTransactionErrorCode::DUPLICATE_TRANSACTION,
            VendorTransactionErrorCode::VENDOR_ID_REQUIRED,
            VendorTransactionErrorCode::ORDER_ID_REQUIRED,
            VendorTransactionErrorCode::AMOUNT_REQUIRED,
            VendorTransactionErrorCode::AMOUNT_NOT_NUMERIC,
            VendorTransactionErrorCode::AMOUNT_NOT_POSITIVE,
            VendorTransactionErrorCode::STATUS_REQUIRED,
            VendorTransactionErrorCode::INVALID_STATUS_TRANSITION,
            VendorTransactionErrorCode::MALFORMED_JSON => $message,
            default => str_starts_with($message, 'invalid_status_transition:')
                ? VendorTransactionErrorCode::INVALID_STATUS_TRANSITION
                : 'transaction_validation_error',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(VendorTransaction $transaction): array
    {
        return [
            'id' => $transaction->getId(),
            'vendorId' => $transaction->getVendorId(),
            'orderId' => $transaction->getOrderId(),
            'projectId' => $transaction->getProjectId(),
            'amount' => $transaction->getAmount(),
            'status' => $transaction->getStatus(),
        ];
    }
}

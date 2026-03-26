<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Entity\VendorTransaction;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\ServiceInterface\VendorTransactionInputResolverServiceInterface;
use App\ServiceInterface\VendorTransactionManagerInterface;
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
        private readonly VendorTransactionInputResolverServiceInterface $inputResolver,
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
            $data = $this->inputResolver->resolveCreateData($request);
            $tx = $this->manager->createTransaction($data);
        } catch (JsonException $exception) {
            return new JsonResponse([
                'error' => VendorTransactionErrorCode::MALFORMED_JSON,
                'jsonErrorCode' => $exception->getCode(),
            ], 400);
        } catch (\InvalidArgumentException $exception) {
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
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
            $status = $this->inputResolver->resolveStatus($request);
            $updated = $this->manager->updateStatus($transaction, $status);
        } catch (JsonException $exception) {
            return new JsonResponse([
                'error' => VendorTransactionErrorCode::MALFORMED_JSON,
                'jsonErrorCode' => $exception->getCode(),
            ], 400);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $this->inputResolver->normalizeErrorCode($exception->getMessage())], 422);
        }

        return new JsonResponse(['id' => $updated->getId(), 'status' => $updated->getStatus()]);
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

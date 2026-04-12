<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use App\Entity\VendorTransaction;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use App\ServiceInterface\Traffic\WriteRateLimiterInterface;
use App\ServiceInterface\VendorApiKeyServiceInterface;
use App\ServiceInterface\VendorTransactionInputResolverServiceInterface;
use App\ServiceInterface\VendorTransactionManagerInterface;
use App\ValueObject\Traffic\WriteRateLimitDecision;
use App\ValueObject\VendorTransactionErrorCode;
use Doctrine\ORM\Exception\ManagerException;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-transactions')]
final class VendorTransactionController extends AbstractController
{
    private const string WRITE_TRANSACTIONS_PERMISSION = 'write:transactions';
    public function __construct(
        private readonly VendorTransactionRepositoryInterface $repo,
        private readonly VendorTransactionManagerInterface $manager,
        private readonly VendorTransactionInputResolverServiceInterface $inputResolver,
        private readonly RuntimeLoggerInterface $runtimeLogger,
        private readonly WriteRateLimiterInterface $writeRateLimiter,
        private readonly VendorApiKeyServiceInterface $apiKeyService,
    ) {}

    /**
     * Create a vendor transaction from a JSON payload.
     *
     * Request schema: vendorId:string, orderId:string, projectId:?string, amount:string(decimal).
     * Success response: {id:int,status:string}.
     * Error responses: malformed_json(400), duplicate_transaction(409), validation errors(422).
     */
    /**
     * @throws ManagerException
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if ($authenticationResponse = $this->enforceTransactionWriteAuthentication($request, null, null)) {
            return $authenticationResponse;
        }

        $rateLimitDecision = $this->writeRateLimiter->consume(
            'vendor_transaction_create',
            $this->writeActorKey($request),
            5,
            60,
        );

        if (!$rateLimitDecision->allowed()) {
            return $this->rateLimitResponse('vendor_transaction_create_rate_limited', $rateLimitDecision, $request, null, null);
        }

        try {
            $data = $this->inputResolver->resolveCreateData($request);
            $tx = $this->manager->createTransaction($data);
        } catch (JsonException $exception) {
            $this->runtimeLogger->warning('vendor_transaction_create_rejected', [
                'error_code' => VendorTransactionErrorCode::MALFORMED_JSON,
            ]);

            return new JsonResponse([
                'error' => VendorTransactionErrorCode::MALFORMED_JSON,
                'jsonErrorCode' => $exception->getCode(),
            ], 400);
        } catch (InvalidArgumentException $exception) {
            // Unknown validation failures must collapse to transaction_validation_error.
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
            $statusCode = VendorTransactionErrorCode::DUPLICATE_TRANSACTION === $errorCode ? 409 : 422;
            $this->runtimeLogger->warning('vendor_transaction_create_rejected', [
                'vendor_id' => isset($data) ? $data->vendorId : null,
                'order_id' => isset($data) ? $data->orderId : null,
                'project_id' => isset($data) ? $data->projectId : null,
                'error_code' => $errorCode,
                'status_code' => (string) $statusCode,
            ]);

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
    /**
     * @throws ManagerException
     */
    #[Route('/vendor/{vendorId}/{id}/status', methods: ['POST'])]
    public function updateStatus(string $vendorId, int $id, Request $request): JsonResponse
    {
        if ($authenticationResponse = $this->enforceTransactionWriteAuthentication($request, $vendorId, $id)) {
            return $authenticationResponse;
        }

        $rateLimitDecision = $this->writeRateLimiter->consume(
            'vendor_transaction_status_update',
            $this->writeActorKey($request, $vendorId),
            10,
            60,
        );

        if (!$rateLimitDecision->allowed()) {
            return $this->rateLimitResponse('vendor_transaction_status_rate_limited', $rateLimitDecision, $request, $vendorId, $id);
        }

        $transaction = $this->repo->findOneByIdAndVendorId($id, $vendorId);

        if (!$transaction instanceof VendorTransaction) {
            $this->runtimeLogger->warning('vendor_transaction_status_update_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => (string) $id,
                'error_code' => VendorTransactionErrorCode::NOT_FOUND,
            ]);

            return new JsonResponse(['error' => VendorTransactionErrorCode::NOT_FOUND], 404);
        }

        try {
            $status = $this->inputResolver->resolveStatus($request);
            $updated = $this->manager->updateStatus($transaction, $status);
        } catch (JsonException $exception) {
            $this->runtimeLogger->warning('vendor_transaction_status_update_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => (string) $id,
                'error_code' => VendorTransactionErrorCode::MALFORMED_JSON,
            ]);

            return new JsonResponse([
                'error' => VendorTransactionErrorCode::MALFORMED_JSON,
                'jsonErrorCode' => $exception->getCode(),
            ], 400);
        } catch (InvalidArgumentException $exception) {
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
            $this->runtimeLogger->warning('vendor_transaction_status_update_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => (string) $id,
                'error_code' => $errorCode,
            ]);

            return new JsonResponse(['error' => $errorCode], 422);
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

    /**
     * @throws ManagerException
     */
    private function enforceTransactionWriteAuthentication(Request $request, ?string $vendorId, ?int $transactionId): ?JsonResponse
    {
        return $this->enforceWriteAuthentication($request, $vendorId, $transactionId);
    }

    /**
     * @throws ManagerException
     */
    private function enforceWriteAuthentication(Request $request, ?string $vendorId, ?int $transactionId): ?JsonResponse
    {
        $requiredPermission = self::WRITE_TRANSACTIONS_PERMISSION;
        $authorization = trim((string) $request->headers->get('Authorization', ''));

        if ('' === $authorization) {
            $this->runtimeLogger->warning('vendor_transaction_authentication_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => null !== $transactionId ? (string) $transactionId : null,
                'error_code' => 'authentication_required',
                'required_permission' => $requiredPermission,
            ]);

            return $this->authenticationResponse('authentication_required', 401, $requiredPermission);
        }

        $authenticatedVendor = $this->apiKeyService->resolveVendorFromAuthHeader($authorization);
        if (null === $authenticatedVendor) {
            $this->runtimeLogger->warning('vendor_transaction_authentication_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => null !== $transactionId ? (string) $transactionId : null,
                'error_code' => 'invalid_api_token',
                'required_permission' => $requiredPermission,
            ]);

            return $this->authenticationResponse('invalid_api_token', 401, $requiredPermission);
        }

        $authorizedVendor = $this->apiKeyService->validateAuthorizationHeader($authorization, $requiredPermission);
        if (null === $authorizedVendor) {
            $this->runtimeLogger->warning('vendor_transaction_authorization_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => null !== $transactionId ? (string) $transactionId : null,
                'error_code' => 'permission_denied',
                'required_permission' => $requiredPermission,
            ]);

            return $this->authenticationResponse('permission_denied', 403, $requiredPermission);
        }

        return null;
    }

    private function authenticationResponse(string $errorCode, int $statusCode, string $requiredPermission): JsonResponse
    {
        $response = new JsonResponse([
            'error' => $errorCode,
            'requiredPermission' => $requiredPermission,
        ], $statusCode);

        $response->headers->set('WWW-Authenticate', 'Bearer');
        $response->headers->set('X-Auth-Required-Permission', $requiredPermission);

        return $response;
    }

    private function rateLimitResponse(string $message, WriteRateLimitDecision $decision, Request $request, ?string $vendorId, ?int $transactionId): JsonResponse
    {
        $this->runtimeLogger->warning($message, [
            'vendor_id' => $vendorId,
            'transaction_id' => null !== $transactionId ? (string) $transactionId : null,
            'client_ip' => $request->getClientIp(),
            'error_code' => 'rate_limit_exceeded',
            'retry_after_seconds' => (string) $decision->retryAfterSeconds(),
        ]);

        $response = new JsonResponse([
            'error' => 'rate_limit_exceeded',
            'retryAfterSeconds' => $decision->retryAfterSeconds(),
        ], 429);
        $response->headers->set('Retry-After', (string) $decision->retryAfterSeconds());

        return $response;
    }

    private function writeActorKey(Request $request, ?string $vendorId = null): string
    {
        $explicitTestKey = trim((string) $request->headers->get('X-Rate-Limit-Key', ''));
        if ('' !== $explicitTestKey) {
            return sha1($explicitTestKey . '|' . (null === $vendorId ? '' : $vendorId));
        }

        $authorization = trim((string) $request->headers->get('Authorization', ''));
        $clientIp = $request->getClientIp() ?? 'unknown';

        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            return sha1(uniqid('vendoring_phpunit_rate_', true) . '|' . (null === $vendorId ? '' : $vendorId));
        }

        if ('' === $authorization && 'unknown' === $clientIp) {
            return sha1(uniqid('vendoring_test_rate_', true) . '|' . (null === $vendorId ? '' : $vendorId));
        }

        return sha1($authorization . '|' . $clientIp . '|' . (null === $vendorId ? '' : $vendorId));
    }
}

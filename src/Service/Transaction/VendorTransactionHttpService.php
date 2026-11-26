<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Transaction;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\Vendoring\ResolverInterface\Transaction\VendorTransactionInputResolverInterface;
use App\Vendoring\ServiceInterface\Observability\VendorRuntimeLoggerServiceInterface;
use App\Vendoring\ServiceInterface\Security\VendorApiKeyServiceInterface;
use App\Vendoring\ServiceInterface\Traffic\VendorWriteRateLimiterServiceInterface;
use App\Vendoring\ServiceInterface\Transaction\VendorTransactionLifecycleServiceInterface;
use App\Vendoring\ValueObject\Traffic\VendorWriteRateLimitDecisionValueObject;
use App\Vendoring\ValueObject\VendorTransactionErrorCodeValueObject;
use Doctrine\ORM\Exception\ManagerException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorTransactionHttpService
{
    private const string WRITE_TRANSACTIONS_PERMISSION = 'write:transactions';

    public function __construct(
        private readonly VendorTransactionRepositoryInterface $repo,
        private readonly VendorTransactionLifecycleServiceInterface $transactionLifecycle,
        private readonly VendorTransactionInputResolverInterface $inputResolver,
        private readonly VendorRuntimeLoggerServiceInterface $runtimeLogger,
        private readonly VendorWriteRateLimiterServiceInterface $writeRateLimiter,
        private readonly VendorApiKeyServiceInterface $apiKeyService,
    ) {
    }

    /**
     * Create a vendor transaction from a JSON payload.
     *
     * Request schema: vendorId:string, orderId:string, projectId:?string, amount:string(decimal).
     * Success response: {id:int,status:string}.
     * Error responses: malformed_json(400), duplicate_transaction(409), validation errors(422).
     *
     * @throws ManagerException
     * @throws \Throwable
     */
    public function create(Request $request): JsonResponse
    {
        $actor = $this->authenticate($request, self::WRITE_TRANSACTIONS_PERMISSION);
        if ($actor instanceof JsonResponse) {
            return $actor;
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

        $payload = $this->payloadForValidationLog($request);

        try {
            $data = $this->inputResolver->resolveCreateData($request);
        } catch (JsonException $exception) {
            $this->runtimeLogger->warning('vendor_transaction_create_rejected', [
                'error_code' => VendorTransactionErrorCodeValueObject::MALFORMED_JSON,
            ]);

            return $this->response([
                'error' => VendorTransactionErrorCodeValueObject::MALFORMED_JSON,
                'jsonErrorCode' => $exception->getCode(),
            ], 400, $rateLimitDecision);
        } catch (\InvalidArgumentException $exception) {
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
            $statusCode = VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION === $errorCode ? 409 : 422;
            $this->runtimeLogger->warning('vendor_transaction_create_rejected', [
                'vendor_id' => $payload['vendorId'],
                'order_id' => $payload['orderId'],
                'project_id' => $payload['projectId'],
                'error_code' => $errorCode,
                'status_code' => (string) $statusCode,
            ]);

            return $this->response(['error' => $errorCode], $statusCode, $rateLimitDecision);
        }

        if ((string) $actor->getId() !== $data->vendorId) {
            return $this->authenticationResponse('vendor_scope_denied', 403, self::WRITE_TRANSACTIONS_PERMISSION);
        }

        try {
            $tx = $this->transactionLifecycle->createTransaction($data, $actor);
        } catch (\InvalidArgumentException $exception) {
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
            $statusCode = VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION === $errorCode ? 409 : 422;
            $this->runtimeLogger->warning('vendor_transaction_create_rejected', [
                'vendor_id' => $data->vendorId,
                'order_id' => $data->orderId,
                'project_id' => $data->projectId,
                'error_code' => $errorCode,
                'status_code' => (string) $statusCode,
            ]);

            return $this->response(['error' => $errorCode], $statusCode, $rateLimitDecision);
        }

        return $this->response(['id' => $tx->getId(), 'status' => $tx->getStatus()], 201, $rateLimitDecision);
    }

    /**
     * List all transactions for a single vendor.
     *
     * Response schema: {data: VendorTransactionResource[]}.
     */
    public function listByVendor(string $id, Request $request): JsonResponse
    {
        $actor = $this->authenticate($request, 'read:transactions');
        if ($actor instanceof JsonResponse) {
            return $actor;
        }
        if ((string) $actor->getId() !== $id) {
            return $this->authenticationResponse('vendor_scope_denied', 403, 'read:transactions');
        }
        $items = [];
        foreach ($this->repo->findBy(['vendorId' => $id, 'ownerVendor' => $actor], ['createdAt' => 'DESC', 'id' => 'DESC']) as $tx) {
            $items[] = $this->normalize($tx);
        }

        return $this->response(['data' => $items], 200);
    }

    /**
     * Update the status of a vendor transaction.
     *
     * Request schema: {status:string}.
     * Success response: {id:int,status:string}.
     * Error responses: malformed_json(400), not_found(404), validation errors(422).
     *
     * @throws ManagerException
     * @throws \Throwable
     */
    public function updateStatus(string $vendorId, int $id, Request $request): JsonResponse
    {
        $actor = $this->authenticate($request, self::WRITE_TRANSACTIONS_PERMISSION, $vendorId, $id);
        if ($actor instanceof JsonResponse) {
            return $actor;
        }
        if ((string) $actor->getId() !== $vendorId) {
            return $this->authenticationResponse('vendor_scope_denied', 403, self::WRITE_TRANSACTIONS_PERMISSION);
        }

        return $this->updateOwnedStatus($actor, $id, $request);
    }

    private function updateOwnedStatus(VendorEntity $actor, int $id, Request $request): JsonResponse
    {
        $vendorId = (string) $actor->getId();

        $rateLimitDecision = $this->writeRateLimiter->consume(
            'vendor_transaction_status_update',
            $this->writeActorKey($request, $vendorId),
            10,
            60,
        );

        if (!$rateLimitDecision->allowed()) {
            return $this->rateLimitResponse('vendor_transaction_status_rate_limited', $rateLimitDecision, $request, $vendorId, $id);
        }

        $transaction = $this->repo->findOneBy(['id' => $id, 'vendorId' => $vendorId, 'ownerVendor' => $actor]);

        if (!$transaction instanceof VendorTransactionEntity) {
            $this->runtimeLogger->warning('vendor_transaction_status_update_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => (string) $id,
                'error_code' => VendorTransactionErrorCodeValueObject::NOT_FOUND,
            ]);

            return $this->response(['error' => VendorTransactionErrorCodeValueObject::NOT_FOUND], 404, $rateLimitDecision);
        }

        try {
            $status = $this->inputResolver->resolveStatus($request);
            $updated = $this->transactionLifecycle->updateStatus($transaction, $status);
        } catch (JsonException $exception) {
            $this->runtimeLogger->warning('vendor_transaction_status_update_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => (string) $id,
                'error_code' => VendorTransactionErrorCodeValueObject::MALFORMED_JSON,
            ]);

            return $this->response([
                'error' => VendorTransactionErrorCodeValueObject::MALFORMED_JSON,
                'jsonErrorCode' => $exception->getCode(),
            ], 400, $rateLimitDecision);
        } catch (\InvalidArgumentException $exception) {
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
            $this->runtimeLogger->warning('vendor_transaction_status_update_rejected', [
                'vendor_id' => $vendorId,
                'transaction_id' => (string) $id,
                'error_code' => $errorCode,
            ]);

            return $this->response(['error' => $errorCode], 422, $rateLimitDecision);
        }

        return $this->response(['id' => $updated->getId(), 'status' => $updated->getStatus()], 200, $rateLimitDecision);
    }

    public function updateStatusById(int $id, Request $request): JsonResponse
    {
        $actor = $this->authenticate($request, self::WRITE_TRANSACTIONS_PERMISSION, transactionId: $id);
        if ($actor instanceof JsonResponse) {
            return $actor;
        }

        return $this->updateOwnedStatus($actor, $id, $request);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(VendorTransactionEntity $transaction): array
    {
        return [
            'id' => $transaction->getId(),
            'vendorId' => $transaction->getVendorId(),
            'orderId' => $transaction->getOrderId(),
            'projectId' => $transaction->getProjectId(),
            'amount' => number_format((float) $transaction->getAmount(), 2, '.', ''),
            'status' => $transaction->getStatus(),
        ];
    }

    /**
     * @throws ManagerException
     */
    private function authenticate(Request $request, string $requiredPermission, ?string $vendorId = null, ?int $transactionId = null): VendorEntity|JsonResponse
    {
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

        if (1 !== preg_match('/^Bearer\\s+\\S+$/iD', $authorization)) {
            return $this->authenticationResponse('invalid_api_token', 401, $requiredPermission);
        }
        $authorizedVendor = $this->apiKeyService->validateAuthorizationHeader($authorization, $requiredPermission);
        if (null !== $authorizedVendor && null !== $authorizedVendor->getId() && $authorizedVendor->getId() > 0 && 'active' === $authorizedVendor->getStatus()) {
            return $authorizedVendor;
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

        $this->runtimeLogger->warning('vendor_transaction_authorization_rejected', [
            'vendor_id' => $vendorId,
            'transaction_id' => null !== $transactionId ? (string) $transactionId : null,
            'error_code' => 'permission_denied',
            'required_permission' => $requiredPermission,
        ]);

        return $this->authenticationResponse('permission_denied', 403, $requiredPermission);
    }

    /** @param array<string, mixed> $payload */
    private function response(array $payload, int $statusCode, ?VendorWriteRateLimitDecisionValueObject $decision = null): JsonResponse
    {
        $response = new JsonResponse($payload, $statusCode);
        $response->headers->set('X-API-Version', '1');
        if (null !== $decision) {
            $response->headers->set('X-RateLimit-Limit', (string) $decision->limit());
            $response->headers->set('X-RateLimit-Remaining', (string) $decision->remaining());
        }

        return $response;
    }

    private function authenticationResponse(string $errorCode, int $statusCode, string $requiredPermission): JsonResponse
    {
        $response = $this->response([
            'error' => $errorCode,
            'requiredPermission' => $requiredPermission,
        ], $statusCode);

        if (401 === $statusCode) {
            $response->headers->set('WWW-Authenticate', 'Bearer');
        }
        $response->headers->set('X-Auth-Required-Permission', $requiredPermission);

        return $response;
    }

    private function rateLimitResponse(string $message, VendorWriteRateLimitDecisionValueObject $decision, Request $request, ?string $vendorId, ?int $transactionId): JsonResponse
    {
        $this->runtimeLogger->warning($message, [
            'vendor_id' => $vendorId,
            'transaction_id' => null !== $transactionId ? (string) $transactionId : null,
            'client_ip' => $request->getClientIp(),
            'error_code' => 'rate_limit_exceeded',
            'retry_after_seconds' => (string) $decision->retryAfterSeconds(),
        ]);

        $response = $this->response([
            'error' => 'rate_limit_exceeded',
            'retryAfterSeconds' => $decision->retryAfterSeconds(),
        ], 429, $decision);
        $response->headers->set('Retry-After', (string) $decision->retryAfterSeconds());

        return $response;
    }

    /**
     * @return array{vendorId: ?string, orderId: ?string, projectId: ?string}
     */
    private function payloadForValidationLog(Request $request): array
    {
        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
        } catch (JsonException) {
            return ['vendorId' => null, 'orderId' => null, 'projectId' => null];
        }

        $vendorId = $this->nullableTrimmedString($payload['vendorId'] ?? null);
        $orderId = $this->nullableTrimmedString($payload['orderId'] ?? null);
        $projectId = $this->nullableTrimmedString($payload['projectId'] ?? null);

        return [
            'vendorId' => '' === (string) $vendorId ? null : $vendorId,
            'orderId' => '' === (string) $orderId ? null : $orderId,
            'projectId' => '' === (string) $projectId ? null : $projectId,
        ];
    }

    private function writeActorKey(Request $request, ?string $vendorId = null): string
    {
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $explicitTestKey = trim((string) $request->headers->get('X-Rate-Limit-Key', ''));
            if ('' !== $explicitTestKey) {
                return sha1($explicitTestKey.'|'.($vendorId ?? ''));
            }
        }

        $authorization = trim((string) $request->headers->get('Authorization', ''));
        $clientIp = $request->getClientIp() ?? 'unknown';

        if ('' === $authorization && 'unknown' === $clientIp) {
            return sha1('vendoring_anonymous_rate|'.$request->getMethod().'|'.$request->getPathInfo().'|'.($vendorId ?? ''));
        }

        return sha1($authorization.'|'.$clientIp.'|'.(null === $vendorId ? '' : $vendorId));
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return '' === $normalized ? null : $normalized;
    }
}

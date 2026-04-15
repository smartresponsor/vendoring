<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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
use Throwable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-transactions')]
final class VendorTransactionController extends AbstractController
{
    private const string WRITE_TRANSACTIONS_PERMISSION = 'write:transactions';
    private const string EVENT_CREATE_REJECTED = 'vendor_transaction_create_rejected';
    private const string EVENT_STATUS_UPDATE_REJECTED = 'vendor_transaction_status_update_rejected';
    private const string EVENT_AUTHENTICATION_REJECTED = 'vendor_transaction_authentication_rejected';
    private const string EVENT_AUTHORIZATION_REJECTED = 'vendor_transaction_authorization_rejected';

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
     * @throws ManagerException
     * @throws Throwable
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if ($authenticationResponse = $this->enforceWriteAuthentication($request, null, null)) {
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

        $payload = $this->payloadForValidationLog($request);

        try {
            $data = $this->inputResolver->resolveCreateData($request);
        } catch (JsonException $exception) {
            return $this->malformedJsonResponse(self::EVENT_CREATE_REJECTED, [], $exception);
        } catch (InvalidArgumentException $exception) {
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
            $statusCode = $this->validationStatusCode($errorCode);

            return $this->validationFailureResponse(
                self::EVENT_CREATE_REJECTED,
                [
                    'vendor_id' => $payload['vendorId'],
                    'order_id' => $payload['orderId'],
                    'project_id' => $payload['projectId'],
                ],
                $errorCode,
                $statusCode,
                true,
            );
        }

        try {
            $tx = $this->manager->createTransaction($data);
        } catch (InvalidArgumentException $exception) {
            $errorCode = $this->inputResolver->normalizeErrorCode($exception->getMessage());
            $statusCode = $this->validationStatusCode($errorCode);

            return $this->validationFailureResponse(
                self::EVENT_CREATE_REJECTED,
                [
                    'vendor_id' => $data->vendorId,
                    'order_id' => $data->orderId,
                    'project_id' => $data->projectId,
                ],
                $errorCode,
                $statusCode,
                true,
            );
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
     * @throws ManagerException
     * @throws Throwable
     */
    #[Route('/vendor/{vendorId}/{id}/status', methods: ['POST'])]
    public function updateStatus(string $vendorId, int $id, Request $request): JsonResponse
    {
        if ($authenticationResponse = $this->enforceWriteAuthentication($request, $vendorId, $id)) {
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
            $this->runtimeLogger->warning(self::EVENT_STATUS_UPDATE_REJECTED, [
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
            return $this->malformedJsonResponse(
                self::EVENT_STATUS_UPDATE_REJECTED,
                ['vendor_id' => $vendorId, 'transaction_id' => (string) $id],
                $exception,
            );
        } catch (InvalidArgumentException $exception) {
            return $this->validationFailureResponse(
                self::EVENT_STATUS_UPDATE_REJECTED,
                ['vendor_id' => $vendorId, 'transaction_id' => (string) $id],
                $this->inputResolver->normalizeErrorCode($exception->getMessage()),
                422,
                false,
            );
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
    private function enforceWriteAuthentication(Request $request, ?string $vendorId, ?int $transactionId): ?JsonResponse
    {
        $requiredPermission = self::WRITE_TRANSACTIONS_PERMISSION;
        $authorization = trim((string) $request->headers->get('Authorization', ''));

        if ('' === $authorization) {
            $this->logAuthorizationRejection(
                self::EVENT_AUTHENTICATION_REJECTED,
                'authentication_required',
                $vendorId,
                $transactionId,
                $requiredPermission,
            );

            return $this->authenticationResponse('authentication_required', 401, $requiredPermission);
        }

        $authorizedVendor = $this->apiKeyService->validateAuthorizationHeader($authorization, $requiredPermission);
        if (null !== $authorizedVendor) {
            return null;
        }

        $authenticatedVendor = $this->apiKeyService->resolveVendorFromAuthHeader($authorization);
        if (null === $authenticatedVendor) {
            $this->logAuthorizationRejection(
                self::EVENT_AUTHENTICATION_REJECTED,
                'invalid_api_token',
                $vendorId,
                $transactionId,
                $requiredPermission,
            );

            return $this->authenticationResponse('invalid_api_token', 401, $requiredPermission);
        }

        $this->logAuthorizationRejection(
            self::EVENT_AUTHORIZATION_REJECTED,
            'permission_denied',
            $vendorId,
            $transactionId,
            $requiredPermission,
        );

        return $this->authenticationResponse('permission_denied', 403, $requiredPermission);
    }

    private function authenticationResponse(string $errorCode, int $statusCode, string $requiredPermission): JsonResponse
    {
        $response = new JsonResponse([
            'error' => $errorCode,
            'requiredPermission' => $requiredPermission,
        ], $statusCode);

        if (401 === $statusCode) {
            $response->headers->set('WWW-Authenticate', 'Bearer');
        }
        $response->headers->set('X-Auth-Required-Permission', $requiredPermission);

        return $response;
    }

    /**
     * @param array<string, scalar|null> $context
     */
    private function malformedJsonResponse(string $eventName, array $context, JsonException $exception): JsonResponse
    {
        $this->runtimeLogger->warning($eventName, $context + [
            'error_code' => VendorTransactionErrorCode::MALFORMED_JSON,
        ]);

        return new JsonResponse([
            'error' => VendorTransactionErrorCode::MALFORMED_JSON,
            'jsonErrorCode' => $exception->getCode(),
        ], 400);
    }

    /**
     * @param array<string, scalar|null> $context
     */
    private function validationFailureResponse(
        string $eventName,
        array $context,
        string $errorCode,
        int $statusCode,
        bool $includeStatusCodeInLog,
    ): JsonResponse
    {
        $logContext = $context + ['error_code' => $errorCode];
        if ($includeStatusCodeInLog) {
            $logContext['status_code'] = (string) $statusCode;
        }

        $this->runtimeLogger->warning($eventName, $logContext);

        return new JsonResponse(['error' => $errorCode], $statusCode);
    }

    private function validationStatusCode(string $errorCode): int
    {
        return VendorTransactionErrorCode::DUPLICATE_TRANSACTION === $errorCode ? 409 : 422;
    }

    private function logAuthorizationRejection(
        string $eventName,
        string $errorCode,
        ?string $vendorId,
        ?int $transactionId,
        string $requiredPermission,
    ): void {
        $this->runtimeLogger->warning($eventName, [
            'vendor_id' => $vendorId,
            'transaction_id' => null !== $transactionId ? (string) $transactionId : null,
            'error_code' => $errorCode,
            'required_permission' => $requiredPermission,
        ]);
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

    /**
     * @return array{vendorId: ?string, orderId: ?string, projectId: ?string}
     */
    private function payloadForValidationLog(Request $request): array
    {
        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return ['vendorId' => null, 'orderId' => null, 'projectId' => null];
        }

        $vendorId = isset($payload['vendorId']) ? trim((string) $payload['vendorId']) : null;
        $orderId = isset($payload['orderId']) ? trim((string) $payload['orderId']) : null;
        $projectId = isset($payload['projectId']) ? trim((string) $payload['projectId']) : null;

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
                return sha1($explicitTestKey . '|' . (null === $vendorId ? '' : $vendorId));
            }
        }

        $authorization = trim((string) $request->headers->get('Authorization', ''));
        $clientIp = $request->getClientIp() ?? 'unknown';

        if ('' === $authorization && 'unknown' === $clientIp) {
            return sha1(
                'vendoring_anonymous_rate'
                . '|' . $request->getMethod()
                . '|' . $request->getPathInfo()
                . '|' . (null === $vendorId ? '' : $vendorId),
            );
        }

        return sha1($authorization . '|' . $clientIp . '|' . (null === $vendorId ? '' : $vendorId));
    }
}

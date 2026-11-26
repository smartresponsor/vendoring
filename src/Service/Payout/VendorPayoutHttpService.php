<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Payout;

use App\Vendoring\Exception\Payout\VendorPayoutProviderNotConfiguredException;
use App\Vendoring\RepositoryInterface\VendorPayoutRepositoryInterface;
use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutServiceInterface;
use App\Vendoring\Trait\Http\VendorApiErrorResponseTrait;
use Doctrine\DBAL\Exception;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Exception\JsonException as HttpFoundationJsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorPayoutHttpService
{
    use VendorApiErrorResponseTrait;

    public function __construct(
        private readonly VendorPayoutServiceInterface $payoutService,
        private readonly VendorPayoutRepositoryInterface $payoutRepository,
        private readonly VendorPayoutRequestServiceInterface $payoutRequestService,
        private readonly VendorFinancialScopeResolverInterface $financialScope,
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        $scope = $this->financialScope->resolve($request, 'write:payouts', null);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
            if ((isset($payload['tenantId']) && $payload['tenantId'] !== $scope->getTenantId())
                || (isset($payload['vendorId']) && $payload['vendorId'] !== $scope->getLedgerVendorId())) {
                return new JsonResponse(['error' => 'financial_scope_denied'], 403);
            }
            $payload['tenantId'] = $scope->getTenantId();
            $payload['vendorId'] = $scope->getLedgerVendorId();
            $dto = $this->payoutRequestService->toCreateDto($payload);
            $id = $this->payoutService->create($dto);
        } catch (HttpFoundationJsonException) {
            return new JsonResponse(['error' => 'malformed_json'], 400);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationErrorResponse(
                $this->normalizePayoutValidationErrorCode($exception->getMessage()),
                'Check payout request fields and try again.',
            );
        } catch (Exception|\JsonException|RandomException) {
            return $this->runtimeErrorResponse('payout_create_failed', 'Check runtime logs for details and retry the operation.');
        }

        if (null === $id) {
            return new JsonResponse(['data' => ['created' => false, 'reason' => 'threshold_not_met']], 200);
        }

        return new JsonResponse(['data' => ['created' => true, 'payoutId' => $id]], 201);
    }

    public function process(string $payoutId, Request $request): JsonResponse
    {
        $scope = $this->financialScope->resolve($request, 'write:payouts', null);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        try {
            $payout = $this->payoutRepository->byId($scope->getTenantId(), $payoutId);
            if (null === $payout || $payout->vendorId !== $scope->getLedgerVendorId()) {
                return new JsonResponse(['error' => 'not_found'], 404);
            }
            $ok = $this->payoutService->process($scope->getTenantId(), $payoutId);
        } catch (VendorPayoutProviderNotConfiguredException) {
            return new JsonResponse(['error' => 'payout_provider_not_configured'], 503);
        } catch (Exception|\JsonException|RandomException) {
            return $this->runtimeErrorResponse('payout_process_failed', 'Check runtime logs for details and retry the operation.');
        }

        return new JsonResponse(['data' => ['processed' => $ok]], $ok ? 200 : 404);
    }

    public function getOne(string $payoutId, Request $request): JsonResponse
    {
        $scope = $this->financialScope->resolve($request, 'read:finance', null);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        try {
            $payout = $this->payoutRepository->byId($scope->getTenantId(), $payoutId);
        } catch (Exception) {
            return $this->runtimeErrorResponse('payout_lookup_failed', 'Check runtime logs for details and retry the operation.');
        }

        if (null === $payout || $payout->vendorId !== $scope->getLedgerVendorId()) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        return new JsonResponse(['data' => $this->payoutRequestService->normalizePayout($payout)], 200);
    }

    private function normalizePayoutValidationErrorCode(string $message): string
    {
        return match (trim($message)) {
            'vendorId required' => 'vendor_id_required',
            'currency required' => 'currency_required',
            'thresholdCents required' => 'threshold_cents_required',
            'retentionFeePercent required' => 'retention_fee_percent_required',
            'retentionFeePercent out_of_range' => 'retention_fee_percent_out_of_range',
            default => 'payout_validation_error',
        };
    }
}

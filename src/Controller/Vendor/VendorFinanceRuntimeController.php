<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ControllerTrait\Vendor\VendorApiErrorResponseTrait;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorTenantQueryRequestResolverServiceInterface;
use Doctrine\DBAL\Exception;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor/runtime')]
final class VendorFinanceRuntimeController extends AbstractController
{
    use VendorApiErrorResponseTrait;

    public function __construct(
        private readonly VendorFinanceRuntimeProjectionBuilderServiceInterface $runtimeProjectionBuilder,
        private readonly VendorTenantQueryRequestResolverServiceInterface $tenantQueryRequestResolver,
    ) {}

    #[Route('/{vendorId}/finance', methods: ['GET'])]
    /** @throws Exception */
    public function finance(string $vendorId, Request $request): JsonResponse
    {
        try {
            $tenantQuery = $this->tenantQueryRequestResolver->resolve($request);
        } catch (VendorApiQueryValidationException $exception) {
            return $this->validationErrorResponse(
                $exception->errorCode(),
                $exception->hint(),
            );
        }

        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $currency = (string) ($request->query->get('currency') ?? 'USD');

        $projection = $this->runtimeProjectionBuilder->build(
            $tenantQuery->tenantId,
            $vendorId,
            $from ? (string) $from : null,
            $to ? (string) $to : null,
            $currency,
        );

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}

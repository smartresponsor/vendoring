<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ControllerTrait\Vendor\VendorApiErrorResponseTrait;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorTenantQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor/runtime')]
final class VendorExternalIntegrationRuntimeController extends AbstractController
{
    use VendorApiErrorResponseTrait;

    public function __construct(
        private readonly VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface $runtimeProjectionBuilder,
        private readonly VendorTenantQueryRequestResolverServiceInterface $tenantQueryRequestResolver,
    ) {}

    #[Route('/{vendorId}/external-integrations', methods: ['GET'])]
    public function show(string $vendorId, Request $request): JsonResponse
    {
        try {
            $tenantQuery = $this->tenantQueryRequestResolver->resolve($request);
        } catch (VendorApiQueryValidationException $exception) {
            return $this->validationErrorResponse(
                $exception->errorCode(),
                $exception->hint(),
            );
        }

        $projection = $this->runtimeProjectionBuilder->build($tenantQuery->tenantId, $vendorId);

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}

<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Integration;

use App\Vendoring\Controller\ApiErrorResponseTrait;
use App\Vendoring\Exception\ApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\TenantQueryRequestResolverInterface;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor/runtime')]
final class VendorExternalIntegrationRuntimeController extends AbstractController
{
    use ApiErrorResponseTrait;

    public function __construct(
        private readonly VendorExternalIntegrationRuntimeViewBuilderInterface $runtimeViewBuilder,
        private readonly TenantQueryRequestResolverInterface $tenantQueryRequestResolver,
    ) {}

    #[Route('/{vendorId}/external-integrations', methods: ['GET'])]
    public function show(string $vendorId, Request $request): JsonResponse
    {
        try {
            $tenantQuery = $this->tenantQueryRequestResolver->resolve($request);
        } catch (ApiQueryValidationException $exception) {
            return $this->validationErrorResponse(
                $exception->errorCode(),
                $exception->hint(),
            );
        }

        $view = $this->runtimeViewBuilder->build($tenantQuery->tenantId, $vendorId);

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

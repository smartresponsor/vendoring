<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Integration;

use App\Controller\ApiErrorResponseTrait;
use App\ServiceInterface\Api\TenantQueryRequestResolverInterface;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
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
        } catch (\InvalidArgumentException) {
            return $this->validationErrorResponse(
                'tenant_id_required',
                'Provide the tenantId query parameter.',
            );
        }

        $view = $this->runtimeViewBuilder->build($tenantQuery->tenantId, $vendorId);

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

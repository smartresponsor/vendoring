<?php

declare(strict_types=1);

namespace App\Controller\Integration;

use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for vendor external integration runtime operations.
 */
#[Route('/api/vendor/runtime')]
final class VendorExternalIntegrationRuntimeController extends AbstractController
{
    public function __construct(
        private readonly VendorExternalIntegrationRuntimeViewBuilderInterface $runtimeViewBuilder,
    ) {
    }

    /**
     * Returns the current read model for the requested resource.
     */
    #[Route('/{vendorId}/external-integrations', methods: ['GET'])]
    public function show(string $vendorId, Request $request): JsonResponse
    {
        $tenantId = (string) ($request->query->get('tenantId') ?? '');
        if ('' === $tenantId) {
            return new JsonResponse(['error' => 'tenantId is required'], 422);
        }

        $view = $this->runtimeViewBuilder->build($tenantId, $vendorId);

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

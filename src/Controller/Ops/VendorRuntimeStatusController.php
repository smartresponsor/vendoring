<?php

declare(strict_types=1);

namespace App\Controller\Ops;

use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for vendor runtime status operations.
 */
#[Route('/api/vendor-runtime-status')]
final class VendorRuntimeStatusController extends AbstractController
{
    public function __construct(private readonly VendorRuntimeStatusViewBuilderInterface $runtimeStatusViewBuilder)
    {
    }

    /**
     * Returns the current read model for the requested resource.
     */
    #[Route('/tenant/{tenantId}/vendor/{vendorId}', methods: ['GET'])]
    public function show(string $tenantId, string $vendorId, Request $request): JsonResponse
    {
        $view = $this->runtimeStatusViewBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $request->query->get('from'),
            to: $request->query->get('to'),
            currency: (string) $request->query->get('currency', 'USD'),
        );

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

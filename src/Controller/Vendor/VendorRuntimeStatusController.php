<?php

declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ServiceInterface\Ops\VendorRuntimeStatusProjectionBuilderServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-runtime-status')]
final class VendorRuntimeStatusController extends AbstractController
{
    public function __construct(private readonly VendorRuntimeStatusProjectionBuilderServiceInterface $runtimeStatusProjectionBuilder) {}

    #[Route('/tenant/{tenantId}/vendor/{vendorId}', methods: ['GET'])]
    public function show(string $tenantId, string $vendorId, Request $request): JsonResponse
    {
        $projection = $this->runtimeStatusProjectionBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $request->query->get('from'),
            to: $request->query->get('to'),
            currency: (string) $request->query->get('currency', 'USD'),
        );

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}

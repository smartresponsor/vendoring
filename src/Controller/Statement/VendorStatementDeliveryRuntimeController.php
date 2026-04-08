<?php

declare(strict_types=1);

namespace App\Controller\Statement;

use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for vendor statement delivery runtime operations.
 */
#[Route('/api/vendor/runtime')]
final class VendorStatementDeliveryRuntimeController extends AbstractController
{
    public function __construct(
        private readonly VendorStatementDeliveryRuntimeViewBuilderInterface $runtimeViewBuilder,
    ) {
    }

    /**
     * Returns the current read model for the requested resource.
     */
    #[Route('/{vendorId}/statement-delivery', methods: ['GET'])]
    public function show(string $vendorId, Request $request): JsonResponse
    {
        $tenantId = (string) ($request->query->get('tenantId') ?? '');
        $from = (string) ($request->query->get('from') ?? '');
        $to = (string) ($request->query->get('to') ?? '');
        $currency = (string) ($request->query->get('currency') ?? 'USD');
        $includeExport = filter_var($request->query->get('includeExport', true), FILTER_VALIDATE_BOOL);

        if ('' === $tenantId || '' === $from || '' === $to) {
            return new JsonResponse(['error' => 'tenantId, from and to are required'], 422);
        }

        $view = $this->runtimeViewBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from,
            to: $to,
            currency: $currency,
            includeExport: $includeExport,
        );

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Finance;

use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for vendor finance runtime operations.
 */
#[Route('/api/vendor/runtime')]
final class VendorFinanceRuntimeController extends AbstractController
{
    public function __construct(private readonly VendorFinanceRuntimeViewBuilderInterface $runtimeViewBuilder)
    {
    }

    /**
     * Executes the finance operation for this runtime surface.
     */
    #[Route('/{vendorId}/finance', methods: ['GET'])]
    public function finance(string $vendorId, Request $request): JsonResponse
    {
        $tenantId = (string) ($request->query->get('tenantId') ?? '');
        if ('' === $tenantId) {
            return new JsonResponse(['error' => 'tenantId required'], 422);
        }

        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $currency = (string) ($request->query->get('currency') ?? 'USD');

        $view = $this->runtimeViewBuilder->build(
            $tenantId,
            $vendorId,
            $from ? (string) $from : null,
            $to ? (string) $to : null,
            $currency,
        );

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

<?php

declare(strict_types=1);

namespace App\Vendoring\Controller;

use App\Vendoring\ServiceInterface\VendorOwnershipViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Read-side HTTP controller for vendor ownership inspection.
 *
 * Exposes the ownership projection for a single vendor through a stable JSON contract. The
 * controller performs no writes and returns `vendor_not_found` when the ownership projection is
 * unavailable for the requested identifier.
 */
#[Route('/api/vendor-ownership')]
final class VendorOwnershipController extends AbstractController
{
    public function __construct(private readonly VendorOwnershipViewBuilderInterface $ownershipViewBuilder) {}

    /**
     * Show ownership information for one vendor.
     *
     * @param int $vendorId Canonical numeric vendor identifier.
     *
     * @return JsonResponse JSON payload containing either `vendor_not_found` or a `data` object with
     *                      the ownership projection built by the ownership view builder.
     */
    #[Route('/vendor/{vendorId}', methods: ['GET'])]
    public function show(int $vendorId): JsonResponse
    {
        $view = $this->ownershipViewBuilder->buildForVendorId($vendorId);

        if (null === $view) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-ownership')]
final class VendorOwnershipController extends AbstractController
{
    public function __construct(private readonly VendorOwnershipViewBuilderInterface $ownershipViewBuilder)
    {
    }

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

<?php

declare(strict_types=1);

namespace App\Controller\Ops;

use App\ServiceInterface\Ops\VendorReleaseBaselineReaderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-release-baseline')]
final class VendorReleaseBaselineController extends AbstractController
{
    public function __construct(private readonly VendorReleaseBaselineReaderInterface $releaseBaselineReader) {}

    #[Route('/tenant/{tenantId}/vendor/{vendorId}', methods: ['GET'])]
    public function show(string $tenantId, string $vendorId, Request $request): JsonResponse
    {
        $view = $this->releaseBaselineReader->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $request->query->get('from'),
            to: $request->query->get('to'),
            currency: (string) $request->query->get('currency', 'USD'),
        );

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

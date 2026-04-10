<?php

declare(strict_types=1);

namespace App\Controller\Statement;

use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor/runtime')]
final class VendorStatementDeliveryRuntimeController extends AbstractController
{
    public function __construct(
        private readonly VendorStatementDeliveryRuntimeViewBuilderInterface $runtimeViewBuilder,
        private readonly VendorStatementRequestResolverInterface $requestResolver,
    ) {}

    #[Route('/{vendorId}/statement-delivery', methods: ['GET'])]
    public function show(string $vendorId, Request $request): JsonResponse
    {
        $runtimeRequest = $this->requestResolver->resolveDeliveryRuntimeRequest($vendorId, $request);
        if (null === $runtimeRequest) {
            return new JsonResponse(['error' => 'tenantId, from and to are required'], 422);
        }

        $view = $this->runtimeViewBuilder->build($runtimeRequest);

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

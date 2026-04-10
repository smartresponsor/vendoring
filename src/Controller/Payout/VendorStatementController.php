<?php

declare(strict_types=1);

namespace App\Controller\Payout;

use App\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payouts/statements')]
final class VendorStatementController extends AbstractController
{
    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly VendorStatementRequestResolverInterface $requestResolver,
    ) {}

    #[Route('/{vendorId}', methods: ['GET'])]
    public function build(string $vendorId, Request $r): JsonResponse
    {
        $dto = $this->requestResolver->resolveStatementRequest($vendorId, $r);
        if (null === $dto) {
            return new JsonResponse(['error' => 'params required'], 422);
        }
        $data = $this->svc->build($dto);

        return new JsonResponse(['data' => $data], 200);
    }
}

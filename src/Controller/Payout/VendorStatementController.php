<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Payout;

use App\Vendoring\Controller\ApiErrorResponseTrait;
use App\Vendoring\Controller\VendorStatementRequestHttpResolutionTrait;
use App\Vendoring\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use Doctrine\DBAL\Exception;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payouts/statements')]
final class VendorStatementController extends AbstractController
{
    use ApiErrorResponseTrait;
    use VendorStatementRequestHttpResolutionTrait;

    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly VendorStatementRequestResolverInterface $requestResolver,
        private readonly StatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
    ) {}

    #[Route('/{vendorId}', methods: ['GET'])]
    /** @throws Exception */
    public function build(string $vendorId, Request $r): JsonResponse
    {
        $dto = $this->resolveStatementRequestOrErrorResponse(
            $vendorId,
            $r,
            $this->statementWindowQueryRequestResolver,
            $this->requestResolver,
        );
        if ($dto instanceof JsonResponse) {
            return $dto;
        }

        $data = $this->svc->build($dto);

        return new JsonResponse(['data' => $data], 200);
    }
}

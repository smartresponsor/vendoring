<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ControllerTrait\Vendor\VendorApiErrorResponseTrait;
use App\Vendoring\ControllerTrait\Vendor\VendorStatementRequestHttpResolutionTrait;
use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use Doctrine\DBAL\Exception;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payouts/statements')]
final class VendorStatementController extends AbstractController
{
    use VendorApiErrorResponseTrait;
    use VendorStatementRequestHttpResolutionTrait;

    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly VendorStatementRequestResolverServiceInterface $requestResolver,
        private readonly VendorStatementWindowQueryRequestResolverServiceInterface $statementWindowQueryRequestResolver,
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

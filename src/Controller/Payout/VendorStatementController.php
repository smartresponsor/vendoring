<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Payout;

use App\Controller\ApiErrorResponseTrait;
use App\Exception\ApiQueryValidationException;
use App\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use Doctrine\DBAL\Exception;
use App\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payouts/statements')]
final class VendorStatementController extends AbstractController
{
    use ApiErrorResponseTrait;

    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly VendorStatementRequestResolverInterface $requestResolver,
        private readonly StatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
    ) {}

    #[Route('/{vendorId}', methods: ['GET'])]
    /** @throws Exception */
    public function build(string $vendorId, Request $r): JsonResponse
    {
        try {
            $this->statementWindowQueryRequestResolver->resolve($r);
        } catch (ApiQueryValidationException $exception) {
            return $this->validationErrorResponse($exception->errorCode(), $exception->hint());
        }

        $dto = $this->requestResolver->resolveStatementRequest($vendorId, $r);
        if (null === $dto) {
            return $this->validationErrorResponse(
                'statement_params_required',
                'Provide tenantId, from, and to query parameters.',
            );
        }
        $data = $this->svc->build($dto);

        return new JsonResponse(['data' => $data], 200);
    }
}

<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Statement;

use App\Controller\ApiErrorResponseTrait;
use App\Controller\StatementQueryValidationResponseTrait;
use App\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor/runtime')]
final class VendorStatementDeliveryRuntimeController extends AbstractController
{
    use ApiErrorResponseTrait;
    use StatementQueryValidationResponseTrait;

    public function __construct(
        private readonly VendorStatementDeliveryRuntimeViewBuilderInterface $runtimeViewBuilder,
        private readonly VendorStatementRequestResolverInterface $requestResolver,
        private readonly StatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
    ) {}

    #[Route('/{vendorId}/statement-delivery', methods: ['GET'])]
    public function show(string $vendorId, Request $request): JsonResponse
    {
        try {
            $this->statementWindowQueryRequestResolver->resolve($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->statementQueryValidationResponse($exception);
        }

        $runtimeRequest = $this->requestResolver->resolveDeliveryRuntimeRequest($vendorId, $request);
        if (null === $runtimeRequest) {
            return $this->validationErrorResponse(
                'statement_runtime_params_required',
                'Provide tenantId, from, and to query parameters.',
            );
        }

        $view = $this->runtimeViewBuilder->build($runtimeRequest);

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

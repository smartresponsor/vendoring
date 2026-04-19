<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Statement;

use App\Vendoring\Controller\ApiErrorResponseTrait;
use App\Vendoring\Exception\ApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor/runtime')]
final class VendorStatementDeliveryRuntimeController extends AbstractController
{
    use ApiErrorResponseTrait;

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
        } catch (ApiQueryValidationException $exception) {
            return $this->validationErrorResponse($exception->errorCode(), $exception->hint());
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

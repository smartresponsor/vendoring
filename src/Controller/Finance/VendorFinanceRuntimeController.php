<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Finance;

use App\Vendoring\Controller\ApiErrorResponseTrait;
use App\Vendoring\Exception\ApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\TenantQueryRequestResolverInterface;
use Doctrine\DBAL\Exception;
use App\Vendoring\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor/runtime')]
final class VendorFinanceRuntimeController extends AbstractController
{
    use ApiErrorResponseTrait;

    public function __construct(
        private readonly VendorFinanceRuntimeViewBuilderInterface $runtimeViewBuilder,
        private readonly TenantQueryRequestResolverInterface $tenantQueryRequestResolver,
    ) {}

    #[Route('/{vendorId}/finance', methods: ['GET'])]
    /** @throws Exception */
    public function finance(string $vendorId, Request $request): JsonResponse
    {
        try {
            $tenantQuery = $this->tenantQueryRequestResolver->resolve($request);
        } catch (ApiQueryValidationException $exception) {
            return $this->validationErrorResponse(
                $exception->errorCode(),
                $exception->hint(),
            );
        }

        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $currency = (string) ($request->query->get('currency') ?? 'USD');

        $view = $this->runtimeViewBuilder->build(
            $tenantQuery->tenantId,
            $vendorId,
            $from ? (string) $from : null,
            $to ? (string) $to : null,
            $currency,
        );

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}

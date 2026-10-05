<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Runtime\Finance;

use App\Vendoring\BuilderInterface\Finance\VendorFinanceRuntimeProjectionBuilderInterface;
use App\Vendoring\Trait\Http\VendorApiErrorResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorRuntimeFinanceShowService
{
    use VendorApiErrorResponseTrait;

    public function __construct(
        private VendorFinanceRuntimeProjectionBuilderInterface $runtimeProjectionBuilder,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $vendorId = $this->attribute($request, 'id') ?? $this->attribute($request, 'slug') ?? $this->attribute($request, 'item') ?? (string) $request->query->get('vendorId', '');
        if ('' === $vendorId) {
            return new JsonResponse(['error' => 'vendor_identifier_required'], 422);
        }

        $projection = $this->runtimeProjectionBuilder->build(
            $vendorId,
            $request->query->get('from') ? (string) $request->query->get('from') : null,
            $request->query->get('to') ? (string) $request->query->get('to') : null,
            (string) ($request->query->get('currency') ?? 'USD'),
        );

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }

    private function attribute(Request $request, string $nameEntity): ?string
    {
        $value = $request->attributes->get($nameEntity);

        return is_scalar($value) && '' !== trim((string) $value) ? trim((string) $value) : null;
    }
}

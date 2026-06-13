<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Release\Baseline;

use App\Vendoring\ServiceInterface\Ops\VendorReleaseBaselineReaderServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorReleaseBaselineShowService
{
    public function __construct(private VendorReleaseBaselineReaderServiceInterface $releaseBaselineReader)
    {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'request_required'], 400);
        }

        $vendorId = $this->attribute($request, 'id') ?? $this->attribute($request, 'slug') ?? $this->attribute($request, 'item') ?? (string) $request->query->get('vendorId', '');
        $tenantId = (string) $request->query->get('tenantId', '');
        if ('' === $vendorId || '' === $tenantId) {
            return new JsonResponse(['error' => 'tenantId_and_vendor_identifier_required'], 422);
        }

        $projection = $this->releaseBaselineReader->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $request->query->get('from'),
            to: $request->query->get('to'),
            currency: (string) $request->query->get('currency', 'USD'),
        );

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }

    private function attribute(Request $request, string $nameEntity): ?string
    {
        $value = $request->attributes->get($nameEntity);

        return is_scalar($value) && '' !== trim((string) $value) ? trim((string) $value) : null;
    }
}

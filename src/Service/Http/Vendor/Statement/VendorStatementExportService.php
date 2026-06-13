<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Statement;

use App\Vendoring\Service\Http\Vendor\Statement\Export\VendorStatementExportService as LegacyVendorStatementExportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorStatementExportService
{
    public function __construct(
        private LegacyVendorStatementExportService $inner,
    ) {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'unsupported_request'], 400);
        }

        $vendorId = $this->resolveVendorId($request);
        if (null === $vendorId) {
            return new JsonResponse(['error' => 'vendor_id_required'], 422);
        }

        return $this->inner->export($vendorId, $request);
    }

    private function resolveVendorId(Request $request): ?string
    {
        foreach (['vendorId', 'id', 'slug', 'item'] as $field) {
            $value = $request->attributes->get($field);
            if (is_scalar($value) && '' !== trim((string) $value)) {
                return trim((string) $value);
            }

            $value = $request->query->get($field);
            if (is_scalar($value) && '' !== trim((string) $value)) {
                return trim((string) $value);
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Vendoring\Policy\Vendor;

final class VendorCategoryReviewAssignmentPolicy
{
    public function normalizePriority(?string $priority): string
    {
        $normalized = strtolower(trim((string) $priority));

        return match ($normalized) {
            'low', 'medium', 'high' => $normalized,
            default => 'medium',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Policy;

final class CategoryReviewAssignmentPolicy
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

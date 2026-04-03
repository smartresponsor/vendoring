<?php

declare(strict_types=1);

namespace App\Entity;

final class CategoryReviewAssignment
{
    public function __construct(
        private readonly string $requestId,
        private readonly string $categoryId,
        private readonly string $assignedReviewer,
        private readonly string $assignedBy,
        private readonly string $priority,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function payload(): array
    {
        return [
            'requestId' => $this->requestId,
            'categoryId' => $this->categoryId,
            'assignedReviewer' => $this->assignedReviewer,
            'assignedBy' => $this->assignedBy,
            'priority' => $this->priority,
        ];
    }
}

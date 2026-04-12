<?php

declare(strict_types=1);

namespace App\Entity;

final readonly class CategoryReviewAssignment
{
    public function __construct(
        private string $requestId,
        private string $categoryId,
        private string $assignedReviewer,
        private string $assignedBy,
        private string $priority,
    ) {}

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

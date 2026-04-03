<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CategoryReviewAssignment;

final class CategoryReviewAssignmentRepository
{
    /** @var list<CategoryReviewAssignment> */
    private array $assignments = [];

    public function save(CategoryReviewAssignment $assignment): void
    {
        $this->assignments[] = $assignment;
    }

    /**
     * @return list<CategoryReviewAssignment>
     */
    public function all(): array
    {
        return $this->assignments;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CategoryReviewAssignment;

/**
 * Doctrine repository for category review assignment records.
 */
final class CategoryReviewAssignmentRepository
{
    /** @var list<CategoryReviewAssignment> */
    private array $assignments = [];

    /**
     * Persists the requested record.
     */
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

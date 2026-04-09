<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CategoryReviewAssignment;
use App\Policy\CategoryReviewAssignmentPolicy;
use App\Repository\CategoryChangeRequestRepository;
use App\Repository\CategoryReviewAssignmentRepository;

/**
 * Application service for catalog review assignment operations.
 */
final class CatalogReviewAssignmentService
{
    public function __construct(
        private readonly CategoryChangeRequestRepository $requestRepository,
        private readonly CategoryReviewAssignmentRepository $assignmentRepository,
        private readonly CategoryReviewAssignmentPolicy $policy,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function assign(string $requestId, string $reviewer, string $assignedBy, ?string $priority = null): array
    {
        $request = $this->requestRepository->byId($requestId);

        if (null === $request) {
            throw new \InvalidArgumentException(sprintf('category_change_request_not_found:%s', $requestId));
        }

        $assignment = new CategoryReviewAssignment(
            $requestId,
            $request->categoryId(),
            trim($reviewer),
            trim($assignedBy),
            $this->policy->normalizePriority($priority),
        );

        $this->assignmentRepository->save($assignment);

        return $assignment->payload();
    }
}

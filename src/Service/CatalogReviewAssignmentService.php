<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\VendorCatalogReviewAssignment;
use App\Policy\CategoryReviewAssignmentPolicy;
use App\Repository\VendorCatalogCategoryChangeRequestRepository;
use App\Repository\VendorCatalogReviewAssignmentRepository;
use InvalidArgumentException;

final readonly class CatalogReviewAssignmentService
{
    public function __construct(
        private VendorCatalogCategoryChangeRequestRepository $requestRepository,
        private VendorCatalogReviewAssignmentRepository $assignmentRepository,
        private CategoryReviewAssignmentPolicy     $policy,
    ) {}

    /**
     * @return array<string, string>
     */
    public function assign(string $requestId, string $reviewer, string $assignedBy, ?string $priority = null): array
    {
        $request = $this->requestRepository->byId($requestId);

        if (null === $request) {
            throw new InvalidArgumentException(sprintf('category_change_request_not_found:%s', $requestId));
        }

        $assignment = new VendorCatalogReviewAssignment(
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

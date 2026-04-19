<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Entity\VendorCatalogReviewAssignment;
use App\Vendoring\Policy\CategoryReviewAssignmentPolicy;
use App\Vendoring\Repository\VendorCatalogCategoryChangeRequestRepository;
use App\Vendoring\Repository\VendorCatalogReviewAssignmentRepository;
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

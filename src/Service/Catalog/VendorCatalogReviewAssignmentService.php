<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Catalog;

use App\Vendoring\Entity\Vendor\VendorCatalogReviewAssignmentEntity;
use App\Vendoring\Policy\VendorCategoryReviewAssignmentPolicy;
use App\Vendoring\RepositoryInterface\VendorCatalogCategoryChangeRequestRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCatalogReviewAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Catalog\VendorCatalogReviewAssignmentServiceInterface;

final readonly class VendorCatalogReviewAssignmentService implements VendorCatalogReviewAssignmentServiceInterface
{
    public function __construct(
        private VendorCatalogCategoryChangeRequestRepositoryInterface $requestRepository,
        private VendorCatalogReviewAssignmentRepositoryInterface $assignmentRepository,
        private VendorCategoryReviewAssignmentPolicy $policy,
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

        $assignment = new VendorCatalogReviewAssignmentEntity($requestId, [
            'requestId' => $requestId,
            'categoryId' => $request->categoryId(),
            'reviewer' => trim($reviewer),
            'assignedBy' => trim($assignedBy),
            'priority' => $this->policy->normalizePriority($priority),
        ]);

        $this->assignmentRepository->save($assignment);

        return $assignment->payload();
    }
}

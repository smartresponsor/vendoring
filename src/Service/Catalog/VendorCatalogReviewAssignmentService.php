<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Catalog;

use App\Vendoring\Entity\Vendor\VendorCatalogReviewAssignmentEntity;
use App\Vendoring\Policy\Vendor\VendorCategoryReviewAssignmentPolicy;
use App\Vendoring\Repository\Vendor\VendorCatalogCategoryChangeRequestRepository;
use App\Vendoring\Repository\Vendor\VendorCatalogReviewAssignmentRepository;
use App\Vendoring\ServiceInterface\Catalog\VendorCatalogReviewAssignmentServiceInterface;
use InvalidArgumentException;

final readonly class VendorCatalogReviewAssignmentService implements VendorCatalogReviewAssignmentServiceInterface
{
    public function __construct(
        private VendorCatalogCategoryChangeRequestRepository $requestRepository,
        private VendorCatalogReviewAssignmentRepository $assignmentRepository,
        private VendorCategoryReviewAssignmentPolicy     $policy,
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

        $assignment = new VendorCatalogReviewAssignmentEntity(
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

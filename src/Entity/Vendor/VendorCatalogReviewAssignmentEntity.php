<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_review_assignment')]
#[ORM\Index(name: 'idx_vendor_catalog_review_assignment_request', columns: ['request_id'])]
#[ORM\Index(name: 'idx_vendor_catalog_review_assignment_reviewer', columns: ['assigned_reviewer'])]
final class VendorCatalogReviewAssignmentEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(name: 'request_id', type: 'string', length: 96)]
    private string $requestId;

    #[ORM\Column(name: 'category_id', type: 'string', length: 96)]
    private string $categoryId;

    #[ORM\Column(name: 'assigned_reviewer', type: 'string', length: 96)]
    private string $assignedReviewer;

    #[ORM\Column(name: 'assigned_by', type: 'string', length: 96)]
    private string $assignedBy;

    #[ORM\Column(type: 'string', length: 32)]
    private string $priority;

    #[ORM\Column(name: 'assigned_at', type: 'datetime_immutable')]
    private DateTimeImmutable $assignedAt;

    public function __construct(string $requestId, string $categoryId, string $assignedReviewer, string $assignedBy, string $priority)
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->requestId = trim($requestId);
        $this->categoryId = trim($categoryId);
        $this->assignedReviewer = trim($assignedReviewer);
        $this->assignedBy = trim($assignedBy);
        $this->priority = trim($priority);
        $this->assignedAt = new DateTimeImmutable();
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return array<string, string> */
    public function payload(): array
    {
        return [
            'id' => $this->id,
            'requestId' => $this->requestId,
            'categoryId' => $this->categoryId,
            'assignedReviewer' => $this->assignedReviewer,
            'assignedBy' => $this->assignedBy,
            'priority' => $this->priority,
            'assignedAt' => $this->assignedAt->format(DATE_ATOM),
        ];
    }
}

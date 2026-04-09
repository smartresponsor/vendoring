<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CategoryChangeRequest;

/**
 * Doctrine repository for category change request records.
 */
final class CategoryChangeRequestRepository
{
    /** @var array<string, CategoryChangeRequest> */
    private array $requests = [];

    /**
     * Persists the requested record.
     */
    public function save(CategoryChangeRequest $request): void
    {
        $this->requests[$request->id()] = $request;
    }

    /**
     * Executes the by id operation for this runtime surface.
     */
    public function byId(string $id): ?CategoryChangeRequest
    {
        return $this->requests[$id] ?? null;
    }
}

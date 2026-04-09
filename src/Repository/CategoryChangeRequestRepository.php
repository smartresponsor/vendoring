<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CategoryChangeRequest;

final class CategoryChangeRequestRepository
{
    /** @var array<string, CategoryChangeRequest> */
    private array $requests = [];

    public function save(CategoryChangeRequest $request): void
    {
        $this->requests[$request->id()] = $request;
    }

    public function byId(string $id): ?CategoryChangeRequest
    {
        return $this->requests[$id] ?? null;
    }
}

<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Search;

interface VendorTfidfSearchServiceInterface
{
    public function addDocument(string $text): int;

    public function finalize(): void;

    /** @return list<array{id:int,score:float}> */
    public function search(string $query, int $limit = 10): array;
}

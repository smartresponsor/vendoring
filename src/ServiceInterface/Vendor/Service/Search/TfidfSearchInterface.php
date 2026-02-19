<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Search;

interface TfidfSearchInterface
{

    public function addDocument(string $text): int;

    public function finalize(): void;

    public function search(string $query, int $limit = 10): array;
}

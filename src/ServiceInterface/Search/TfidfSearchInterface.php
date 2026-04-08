<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Search;

/**
 * Application contract for tfidf search operations.
 */
interface TfidfSearchInterface
{
    /**
     * Executes the add document operation for this runtime surface.
     */
    public function addDocument(string $text): int;

    /**
     * Executes the finalize operation for this runtime surface.
     */
    public function finalize(): void;

    /** @return list<array{id:int,score:float}> */
    public function search(string $query, int $limit = 10): array;
}

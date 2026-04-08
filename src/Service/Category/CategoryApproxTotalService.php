<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Category;

use App\ServiceInterface\Category\CategoryApproxTotalServiceInterface;

/**
 * Application service for category approx total operations.
 */
final class CategoryApproxTotalService implements CategoryApproxTotalServiceInterface
{
    public function __construct(private string $file = '')
    {
    }

    /** @return array{value:int,accuracy:string} */
    public function get(string $key, bool $withTotal): array
    {
        if ($withTotal) {
            return ['value' => 0, 'accuracy' => 'exact'];
        }
        if (!is_file($this->file)) {
            return ['value' => 0, 'accuracy' => 'approx'];
        }

        $contents = file_get_contents($this->file);
        $data = is_string($contents) ? json_decode($contents, true) : null;
        $map = is_array($data) ? $data : [];
        $value = $map[$key] ?? 0;

        return ['value' => is_numeric($value) ? (int) $value : 0, 'accuracy' => 'approx'];
    }
}

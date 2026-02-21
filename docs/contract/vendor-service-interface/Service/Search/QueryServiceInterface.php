<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Search;

interface QueryServiceInterface
{

    public function query(string $q): array;
}


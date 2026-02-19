<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Compliance\Access;

interface ReviewerInterface
{

    public function review(string $snapshot): array;
}

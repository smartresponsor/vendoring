<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Compliance\Data;

interface ClassifierInterface
{

    public function classify(array $record): array;
}


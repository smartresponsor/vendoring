<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\ControllerInterface\Api\Controller;

interface ProfileControllerInterface
{

    public function list(array $query): array;

    public function get(string $id): array;
}

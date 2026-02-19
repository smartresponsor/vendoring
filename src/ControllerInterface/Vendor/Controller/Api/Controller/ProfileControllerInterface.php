<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface\Vendor\Controller\Api\Controller;

interface ProfileControllerInterface
{

    public function list(array $query): array;

    public function get(string $id): array;
}

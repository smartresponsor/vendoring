<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\InfrastructureInterface\Client\Integration\Crm;

interface ClientInterface
{

    public function send(array $payload): bool;
}

<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\InfrastructureInterface\Client\Vendor\Infrastructure\Client;

interface ClientInterface
{

    public function ping(): string;
}

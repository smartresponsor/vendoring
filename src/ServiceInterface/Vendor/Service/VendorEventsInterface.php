<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service;

interface VendorEventsInterface
{

    public function __construct(private EventPort $bus);

    public function registered(string $id, string $name): void;
}

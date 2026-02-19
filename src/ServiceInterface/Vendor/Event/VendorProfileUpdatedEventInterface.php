<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Event;

interface VendorProfileUpdatedEventInterface
{

    public function __construct(public readonly VendorProfile $profile);
}

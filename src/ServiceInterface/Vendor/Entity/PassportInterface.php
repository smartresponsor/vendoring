<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Entity;

interface PassportInterface
{

    public function __construct(private string $vendorId, private PassportNumber $num);

    public function vendorId(): string;

    public function number(): PassportNumber;
}

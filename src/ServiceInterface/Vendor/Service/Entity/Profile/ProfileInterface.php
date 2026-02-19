<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Entity\Profile;

interface ProfileInterface
{

    public function __construct(private string $id, private string $vendorId, private string $name);

    public function id(): string;

    public function vendorId(): string;

    public function name(): string;
}

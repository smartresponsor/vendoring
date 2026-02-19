<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service;

interface PdoRepoTestInterface
{

    public function setUp(): void;

    public function testCrud(): void;
}

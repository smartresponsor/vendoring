<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Privacy\FLE;

interface EncryptorInterface
{

    public function encrypt(string $tenantKey, string $plaintext): string;

    public function decrypt(string $tenantKey, string $ciphertext): string;
}

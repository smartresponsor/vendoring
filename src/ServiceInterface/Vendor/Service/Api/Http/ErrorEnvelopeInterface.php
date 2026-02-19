<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Api\Http;

interface ErrorEnvelopeInterface
{

    public function build(string $code, string $message, array $details = [], ?string $reqId = null): array;
}

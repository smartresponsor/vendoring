<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Command;

interface VendorCommandJsonEncoderServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     *
     * @throws \JsonException
     */
    public function encode(array $payload): string;
}

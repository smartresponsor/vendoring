<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Command;

use App\Vendoring\ServiceInterface\Command\VendorCommandJsonEncoderServiceInterface;
use JsonException;

final class VendorCommandJsonEncoderService implements VendorCommandJsonEncoderServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     * @throws JsonException
     */
    public function encode(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}

<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\WebhooksConsumer;

use App\Vendoring\ServiceInterface\WebhooksConsumer\VendorWebhooksConsumerServiceInterface;

final class VendorWebhooksConsumerService implements VendorWebhooksConsumerServiceInterface
{
    public function ok(): bool
    {
        return true;
    }
}

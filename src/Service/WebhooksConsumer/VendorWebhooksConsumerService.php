<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\WebhooksConsumer;

use App\ServiceInterface\WebhooksConsumer\VendorWebhooksConsumerServiceInterface;

/**
 * Application service for vendor webhooks consumer operations.
 */
final class VendorWebhooksConsumerService implements VendorWebhooksConsumerServiceInterface
{
    /**
     * Executes the ok operation for this runtime surface.
     */
    public function ok(): bool
    {
        return true;
    }
}

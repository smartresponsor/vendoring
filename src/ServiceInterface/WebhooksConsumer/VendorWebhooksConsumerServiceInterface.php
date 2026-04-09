<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\WebhooksConsumer;

/**
 * Application contract for vendor webhooks consumer service operations.
 */
interface VendorWebhooksConsumerServiceInterface
{
    /**
     * Executes the ok operation for this runtime surface.
     */
    public function ok(): bool;
}

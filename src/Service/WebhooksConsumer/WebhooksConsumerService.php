<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\WebhooksConsumer;

use App\ServiceInterface\WebhooksConsumer\WebhooksConsumerServiceInterface;

final class WebhooksConsumerService implements WebhooksConsumerServiceInterface
{
    public function ok(): bool
    {
        return true;
    }
}

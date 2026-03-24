<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\WebhooksConsumer;

interface WebhooksConsumerServiceInterface
{
    public function ok(): bool;
}

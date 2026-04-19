<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\WebhooksConsumer;

use App\Vendoring\Service\WebhooksConsumer\VendorWebhooksConsumerService;
use PHPUnit\Framework\TestCase;

final class VendorWebhooksConsumerServiceTest extends TestCase
{
    public function testOkReturnsTrueForReadyConsumerSurface(): void
    {
        self::assertTrue((new VendorWebhooksConsumerService())->ok());
    }
}

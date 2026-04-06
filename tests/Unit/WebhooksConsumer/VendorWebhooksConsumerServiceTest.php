<?php

declare(strict_types=1);

namespace App\Tests\Unit\WebhooksConsumer;

use App\Service\WebhooksConsumer\VendorWebhooksConsumerService;
use PHPUnit\Framework\TestCase;

final class VendorWebhooksConsumerServiceTest extends TestCase
{
    public function testOkReturnsTrueForReadyConsumerSurface(): void
    {
        self::assertTrue((new VendorWebhooksConsumerService())->ok());
    }
}

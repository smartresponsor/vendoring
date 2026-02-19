<?php declare(strict_types=1);
namespace App\Service\WebhooksConsumer;
use App\ServiceInterface\WebhooksConsumer\WebhooksConsumerInterface;
final class WebhooksConsumerService implements WebhooksConsumerInterface {
    public function ok(): bool { return true; }
}

<?php

declare(strict_types=1);

use App\Observability\Service\CorrelationContext;
use App\Observability\Service\MetricEmitter;
use App\Observability\Service\RuntimeLogger;
use App\Service\Policy\OutboundOperationPolicy;
use App\Service\Reliability\FileOutboundCircuitBreaker;
use App\Service\Statement\VendorStatementMailerService;
use App\Tests\Support\Statement\FakeMailer;
use Symfony\Component\HttpFoundation\RequestStack;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$policy = new OutboundOperationPolicy();
$breakerDir = sys_get_temp_dir() . '/vendoring-fault-smoke-' . bin2hex(random_bytes(4));
$breaker = new FileOutboundCircuitBreaker($breakerDir);
$scopeKey = 'tenant-1:vendor-1';
$breaker->recordFailure('statement_mail_send', $scopeKey, 2, 60);
$breaker->recordFailure('statement_mail_send', $scopeKey, 2, 60);

/** @var \Symfony\Component\Mailer\MailerInterface $mailer */
$mailer = new FakeMailer();

$service = new VendorStatementMailerService(
    $mailer,
    new MetricEmitter(),
    new RuntimeLogger(new CorrelationContext(), new RequestStack()),
    $policy,
    $breaker,
);

$result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '', 'March 2026');

if (true === ($result['ok'] ?? false)) {
    fwrite(STDERR, "expected circuit-open failure\n");
    exit(1);
}

if ('statement_mail_circuit_open' !== ($result['message'] ?? null)) {
    fwrite(STDERR, "unexpected message\n");
    exit(1);
}

if ('open' !== ($result['circuitState'] ?? null)) {
    fwrite(STDERR, "unexpected circuit state\n");
    exit(1);
}

echo "outbound fault tolerance contract smoke passed\n";

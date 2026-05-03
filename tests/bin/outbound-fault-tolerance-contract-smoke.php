<?php

declare(strict_types=1);

use App\Vendoring\Service\Observability\VendorCorrelationContextService;
use App\Vendoring\Service\Observability\VendorMetricEmitterService;
use App\Vendoring\Service\Observability\VendorRuntimeLoggerService;
use App\Vendoring\Service\Policy\VendorOutboundOperationPolicyService;
use App\Vendoring\Service\Reliability\VendorOutboundCircuitBreakerService;
use App\Vendoring\Service\Statement\VendorStatementMailerService;
use App\Vendoring\Tests\Support\Statement\FakeMailer;
use Symfony\Component\HttpFoundation\RequestStack;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$policy = new VendorOutboundOperationPolicyService();
$breakerDir = sys_get_temp_dir() . '/vendoring-fault-smoke-' . bin2hex(random_bytes(4));
$breaker = new VendorOutboundCircuitBreakerService($breakerDir);
$scopeKey = 'tenant-1:vendor-1';
$breaker->recordFailure('statement_mail_send', $scopeKey, 2, 60);
$breaker->recordFailure('statement_mail_send', $scopeKey, 2, 60);

$mailer = new FakeMailer();

$service = new VendorStatementMailerService(
    $mailer,
    new VendorMetricEmitterService(),
    new VendorRuntimeLoggerService(new VendorCorrelationContextService(), new RequestStack()),
    $policy,
    $breaker,
);

$result = $service->send('tenant-1', 'vendor-1', 'vendor@example.com', '', 'March 2026');

if ($result['ok']) {
    fwrite(STDERR, "expected circuit-open failure\n");
    exit(1);
}

if ('statement_mail_circuit_open' !== $result['message']) {
    fwrite(STDERR, "unexpected message\n");
    exit(1);
}

if ('open' !== $result['circuitState']) {
    fwrite(STDERR, "unexpected circuit state\n");
    exit(1);
}

echo "outbound fault tolerance contract smoke passed\n";

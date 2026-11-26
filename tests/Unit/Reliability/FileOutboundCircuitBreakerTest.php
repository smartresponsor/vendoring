<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Reliability;

use App\Vendoring\Service\Reliability\VendorOutboundCircuitBreakerService;
use PHPUnit\Framework\TestCase;

final class FileOutboundCircuitBreakerTest extends TestCase
{
    public function testBreakerOpensAfterThresholdAndShortCircuitsUntilCooldownExpires(): void
    {
        $dir = sys_get_temp_dir().'/vendoring-breaker-'.bin2hex(random_bytes(4));
        $breaker = new VendorOutboundCircuitBreakerService($dir);

        $initial = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('closed', $initial['state']);
        self::assertTrue($initial['allowRequest']);

        $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        $afterOne = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('closed', $afterOne['state']);
        self::assertTrue($afterOne['allowRequest']);

        $afterTwo = $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('open', $afterTwo['state']);
        self::assertFalse($afterTwo['allowRequest']);

        $current = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('open', $current['state']);
        self::assertFalse($current['allowRequest']);
    }

    public function testLegacyOpenStateAllowsOnlyOneProbeDuringCooldown(): void
    {
        $dir = sys_get_temp_dir().'/vendoring-breaker-'.bin2hex(random_bytes(8));
        mkdir($dir, 0700);
        try {
            file_put_contents($dir.'/mail__scope.json', json_encode(['state' => 'open', 'openedAt' => time() - 61, 'failureCount' => 3], JSON_THROW_ON_ERROR));
            $first = new VendorOutboundCircuitBreakerService($dir);
            $second = new VendorOutboundCircuitBreakerService($dir);
            self::assertTrue($first->currentState('mail', 'scope', 3, 60)['allowRequest']);
            self::assertFalse($second->currentState('mail', 'scope', 3, 60)['allowRequest']);
            self::assertSame('open', $second->recordFailure('mail', 'scope', 3, 60)['state']);
            $first->recordSuccess('mail', 'scope');
            self::assertTrue($second->currentState('mail', 'scope', 3, 60)['allowRequest']);
        } finally {
            (new \Symfony\Component\Filesystem\Filesystem())->remove($dir);
        }
    }

    public function testConcurrentWorkersDoNotLoseFailureIncrements(): void
    {
        $dir = sys_get_temp_dir().'/vendoring-breaker-'.bin2hex(random_bytes(8));
        mkdir($dir, 0700);
        $processes = [];
        try {
            $code = 'require $argv[1]; $b = new \App\Vendoring\Service\Reliability\VendorOutboundCircuitBreakerService($argv[2]); for ($i=0;$i<20;++$i) {$b->recordFailure("mail","scope",1000,60);}';
            for ($i = 0; $i < 6; ++$i) {
                $process = proc_open([PHP_BINARY, '-r', $code, dirname(__DIR__, 3).'/vendor/autoload.php', $dir], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
                self::assertIsResource($process);
                fclose($pipes[0]);
                $processes[] = [$process, $pipes[1], $pipes[2]];
            }
            foreach ($processes as [$process, $out, $err]) {
                stream_get_contents($out);
                $error = stream_get_contents($err);
                fclose($out);
                fclose($err);
                self::assertSame(0, proc_close($process), (string) $error);
            }
            self::assertSame(120, (new VendorOutboundCircuitBreakerService($dir))->currentState('mail', 'scope', 1000, 60)['failureCount']);
        } finally {
            (new \Symfony\Component\Filesystem\Filesystem())->remove($dir);
        }
    }

    public function testBreakerResetsAfterSuccess(): void
    {
        $dir = sys_get_temp_dir().'/vendoring-breaker-'.bin2hex(random_bytes(4));
        $breaker = new VendorOutboundCircuitBreakerService($dir);

        $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 1, 60);
        $open = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 1, 60);
        self::assertSame('open', $open['state']);

        $breaker->recordSuccess('statement_mail_send', 'tenant-1:vendor-1');
        $closed = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 1, 60);
        self::assertSame('closed', $closed['state']);
        self::assertTrue($closed['allowRequest']);
    }
}

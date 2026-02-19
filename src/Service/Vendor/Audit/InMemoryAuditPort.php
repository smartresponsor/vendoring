<?php declare(strict_types=1);
namespace App\Service\Vendor\Audit; use SmartResponsor\Vendor\Port\Audit\AuditPort; final class InMemoryAuditPort implements AuditPort{public array $log=[]; public function append(string $actor,string $action,string $target,string $result):void{$this->log[]=compact('actor','action','target','result');}}

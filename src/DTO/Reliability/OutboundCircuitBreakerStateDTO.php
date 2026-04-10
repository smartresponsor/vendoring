<?php

declare(strict_types=1);

namespace App\DTO\Reliability;

final readonly class OutboundCircuitBreakerStateDTO
{
    public function __construct(
        public string $operation,
        public string $scopeKey,
        public string $state,
        public int $failureCount,
        public int $threshold,
        public int $cooldownSeconds,
        public bool $allowRequest,
    ) {
    }

    /**
     * @return array{
     *   operation:string,
     *   scopeKey:string,
     *   state:string,
     *   failureCount:int,
     *   threshold:int,
     *   cooldownSeconds:int,
     *   allowRequest:bool
     * }
     */
    public function toArray(): array
    {
        return [
            'operation' => $this->operation,
            'scopeKey' => $this->scopeKey,
            'state' => $this->state,
            'failureCount' => $this->failureCount,
            'threshold' => $this->threshold,
            'cooldownSeconds' => $this->cooldownSeconds,
            'allowRequest' => $this->allowRequest,
        ];
    }
}

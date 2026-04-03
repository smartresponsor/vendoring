<?php

declare(strict_types=1);

namespace App\ServiceInterface\Observability;

interface MetricCollectorInterface
{
    /**
     * @param array<string, string> $tags
     */
    public function increment(string $name, array $tags = []): void;
}

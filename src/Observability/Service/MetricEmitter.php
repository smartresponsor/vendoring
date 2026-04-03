<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\MetricCollectorInterface;

/**
 * In-memory metric collector used for tests and inspection-only runtime scenarios.
 */
final class MetricEmitter implements MetricCollectorInterface
{
    /**
     * @var list<array{name:string,tags:array<string,string>}>
     */
    private array $increments = [];

    /**
     * {@inheritdoc}
     */
    public function increment(string $name, array $tags = []): void
    {
        $this->increments[] = [
            'name' => $name,
            'tags' => $tags,
        ];
    }

    /**
     * Return the inspection snapshot of in-memory metric increments.
     *
     * @return list<array{name:string,tags:array<string,string>}>
     */
    public function snapshot(): array
    {
        return $this->increments;
    }
}

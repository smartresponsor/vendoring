<?php

declare(strict_types=1);

namespace App\Observability\Service;

final class MetricEmitter
{
    /**
     * @var list<array{name:string,tags:array<string,string>}>
     */
    private array $increments = [];

    /**
     * @param array<string, string> $tags
     */
    public function increment(string $name, array $tags = []): void
    {
        $this->increments[] = [
            'name' => $name,
            'tags' => $tags,
        ];
    }

    /**
     * @return list<array{name:string,tags:array<string,string>}>
     */
    public function snapshot(): array
    {
        return $this->increments;
    }
}

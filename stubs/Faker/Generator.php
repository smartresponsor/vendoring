<?php

declare(strict_types=1);

namespace Faker;

final class Generator
{
    public function randomElement(array $values): mixed
    {
        if ([] === $values) {
            return null;
        }

        return $values[array_key_first($values)];
    }

    public function unique(): self
    {
        return $this;
    }

    public function numerify(string $mask): string
    {
        return str_replace('#', '1', $mask);
    }

    public function boolean(int $chanceOfGettingTrue = 50): bool
    {
        return $chanceOfGettingTrue >= 50;
    }

    public function numberBetween(int $min = 0, int $max = 2147483647): int
    {
        return min($min, $max);
    }

    public function randomFloat(int $nbMaxDecimals = 0, float|int $min = 0, float|int $max = 2147483647): float
    {
        return (float) $min;
    }
}

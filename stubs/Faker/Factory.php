<?php

declare(strict_types=1);

namespace Faker;

final class Factory
{
    public static function create(string $locale = 'en_US'): Generator
    {
        return new Generator();
    }
}

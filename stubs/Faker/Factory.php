<?php

declare(strict_types=1);

namespace Faker;

final class Factory
{
    /** @noinspection PhpUnusedParameterInspection */
    public static function create(string $_locale = 'en_US'): Generator
    {
        return new Generator();
    }
}

<?php

declare(strict_types=1);

return array_filter([
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => class_exists(Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class) ? ['dev' => true, 'test' => true] : null,
    App\Vendoring\VendoringBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => class_exists(Symfony\Bundle\TwigBundle\TwigBundle::class) ? ['all' => true] : null,
    Nelmio\ApiDocBundle\NelmioApiDocBundle::class => class_exists(Nelmio\ApiDocBundle\NelmioApiDocBundle::class) ? ['all' => true] : null,
]);
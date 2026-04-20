<?php

declare(strict_types=1);

return array_filter([
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    App\Vendoring\VendoringBundle::class => ['all' => true],
]);
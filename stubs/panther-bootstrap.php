<?php

declare(strict_types=1);

if (!class_exists(\Symfony\Component\Panther\Client::class)) {
    require_once __DIR__ . '/Symfony/Component/Panther/Client.php';
}

if (!class_exists(\Symfony\Component\Panther\PantherTestCase::class)) {
    require_once __DIR__ . '/Symfony/Component/Panther/PantherTestCase.php';
}

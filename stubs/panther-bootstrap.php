<?php

declare(strict_types=1);

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(Client::class)) {
    require_once __DIR__ . '/Symfony/Component/Panther/Client.php';
}

if (!class_exists(PantherTestCase::class)) {
    require_once __DIR__ . '/Symfony/Component/Panther/PantherTestCase.php';
}

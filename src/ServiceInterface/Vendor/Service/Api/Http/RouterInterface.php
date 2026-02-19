<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Api\Http;

interface RouterInterface
{

    public function __construct(private $service);

    public function handle(array $req): array;
}

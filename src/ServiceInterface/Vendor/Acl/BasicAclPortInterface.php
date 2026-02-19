<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Acl;

interface BasicAclPortInterface
{

    public function __construct(private array $rules);

    public function allow(string $actor, string $action): bool;
}

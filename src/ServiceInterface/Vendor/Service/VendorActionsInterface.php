<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service;

interface VendorActionsInterface
{

    public function __construct(private AclPort $acl, private AuditPort $audit);

    public function perform(string $actor, string $action, string $target): void;
}

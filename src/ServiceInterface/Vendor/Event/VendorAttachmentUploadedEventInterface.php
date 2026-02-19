<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Event;

interface VendorAttachmentUploadedEventInterface
{

    public function __construct(public readonly VendorAttachment $attachment);
}

<?php
declare(strict_types=1);

namespace App\Bridge\Vendor;

use App\Event\Vendor\VendorCreatedEvent;
use App\Event\Vendor\VendorVerifiedEvent;
use App\Event\Vendor\VendorActivatedEvent;
use App\Event\Vendor\DocumentUploadedEvent;
use App\Event\Vendor\VendorPayoutRequestedEvent;
use App\Event\Vendor\VendorProfileUpdatedEvent;

final class EventMapper
{
    /** Map domain event FQCN to string channel */
    public function name(object $event): string
    {
        return match (true) {
            $event instanceof VendorCreatedEvent => 'vendor.created',
            $event instanceof VendorVerifiedEvent => 'vendor.verified',
            $event instanceof VendorActivatedEvent => 'vendor.activated',
            $event instanceof DocumentUploadedEvent => 'vendor.document.uploaded',
            $event instanceof VendorPayoutRequestedEvent => 'vendor.payout.requested',
            $event instanceof VendorProfileUpdatedEvent => 'vendor.profile.updated',
            default => throw new \InvalidArgumentException('Unknown vendor event: ' . $event::class),
        };
    }
}

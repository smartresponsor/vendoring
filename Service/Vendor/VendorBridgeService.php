<?php
declare(strict_types=1);

namespace App\Bridge\Vendor;

use App\DTO\Vendor\VendorCreateDTO;
use App\DTO\Vendor\VendorBillingDTO;
use App\DTO\Vendor\VendorProfileDTO;
use App\Entity\Vendor\Vendor;
use App\Service\Vendor\VendorService;
use App\Service\Vendor\VendorBillingService;
use App\Service\Vendor\VendorProfileService;
use App\Service\OutboxPublisher;
use App\EventBus\SimpleDomainEvent;
use App\Service\IdempotencyKey;

final class VendorBridgeService
{
    public function __construct(
        private readonly Transaction $tx,
        private readonly VendorService $vendorService,
        private readonly VendorBillingService $billingService,
        private readonly VendorProfileService $profileService,
        private readonly OutboxPublisher $outbox,
        private readonly EventMapper $mapper,
        private readonly BridgeConfig $cfg = new BridgeConfig(),
    ) {}

    public function createVendorWithBasics(
        VendorCreateDTO $vendorDto,
        ?VendorBillingDTO $billingDto = null,
        ?VendorProfileDTO $profileDto = null
    ): Vendor {
        return $this->tx->run(function () use ($vendorDto, $billingDto, $profileDto) {
            $vendor = $this->vendorService->create($vendorDto);

            if ($billingDto && $billingDto->vendorId === $vendor->getId()) {
                $this->billingService->upsert($vendor, $billingDto);
            }

            if ($profileDto && $profileDto->vendorId === $vendor->getId()) {
                $this->profileService->upsert($vendor, $profileDto);
            }

            // publish "vendor.created" into Outbox explicitly (in addition to domain subscriber)
            $payload = ['vendorId' => $vendor->getId()];
            $this->outbox->enqueue(new SimpleDomainEvent(
                'vendor.created', 'Vendor', $vendor->getId(), $payload,
                IdempotencyKey::make('vendor.created', $vendor->getId(), $payload)
            ));

            return $vendor;
        });
    }
}

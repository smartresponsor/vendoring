<?php

declare(strict_types=1);

namespace App\ServiceInterface\Payout;

use App\DTO\Payout\CreatePayoutDTO;

interface VendorPayoutServiceInterface
{
    public function create(CreatePayoutDTO $dto): ?string;

    public function process(string $payoutId): bool;
}

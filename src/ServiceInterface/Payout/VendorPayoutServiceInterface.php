<?php

declare(strict_types=1);

namespace App\ServiceInterface\Payout;

use App\DTO\Payout\CreatePayoutDTO;

/**
 * Application contract for vendor payout service operations.
 */
interface VendorPayoutServiceInterface
{
    /**
     * Creates the requested resource from the supplied input.
     */
    public function create(CreatePayoutDTO $dto): ?string;

    /**
     * Processes the requested runtime operation.
     */
    public function process(string $payoutId): bool;
}

<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Payout;

use App\Vendoring\DTO\Payout\VendorCreatePayoutDTO;
use Doctrine\DBAL\Exception;
use JsonException;
use Random\RandomException;

interface VendorPayoutServiceInterface
{
    /**
     * @throws Exception
     * @throws JsonException
     * @throws RandomException
     */
    public function create(VendorCreatePayoutDTO $dto): ?string;

    /**
     * @throws Exception
     * @throws JsonException
     * @throws RandomException
     */
    public function process(string $payoutId): bool;
}

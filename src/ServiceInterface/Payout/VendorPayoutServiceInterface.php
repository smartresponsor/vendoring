<?php

declare(strict_types=1);

namespace App\ServiceInterface\Payout;

use App\DTO\Payout\CreatePayoutDTO;
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
    public function create(CreatePayoutDTO $dto): ?string;

    /**
     * @throws Exception
     * @throws JsonException
     * @throws RandomException
     */
    public function process(string $payoutId): bool;
}

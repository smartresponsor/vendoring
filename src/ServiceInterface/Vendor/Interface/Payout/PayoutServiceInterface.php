<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor\Interface\Payout;

use App\DTO\Vendor\Payout\PayoutInitDTO;
use App\Entity\Vendor\Payout\PayoutBatch;

interface PayoutServiceInterface
{
    public function init(PayoutInitDTO $dto): PayoutBatch;

    public function finalize(string $batchId): PayoutBatch;

    /** @return array{ok:bool, ref:?string} */
    public function pay(string $batchId): array;
}

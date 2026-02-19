<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Payout;

interface PayoutServiceInterface
{

    public function __construct(private readonly PayoutRepositoryInterface $repo, private readonly LedgerEntryRepository $ledgerRepo, private readonly LedgerService $ledger, private readonly MetricEmitter $metrics);

    public function create(CreatePayoutDTO $dto): ?string;

    public function process(string $payoutId): bool;
}

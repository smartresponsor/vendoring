<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\EntityInterface\Vendor\Payout;

use DateTimeImmutable;

/**
 *
 */
interface PayoutAccountInterface
{
    /**
     * @return string
     */
    public function id(): string;

    /**
     * @return string
     */
    public function tenantId(): string;

    /**
     * @return string
     */
    public function vendorId(): string;

    /** stripe_connect|paypal|bank */
    public function provider(): string;

    /** acct_xxx / email / IBAN */
    public function accountRef(): string;

    /**
     * @return string
     */
    public function currency(): string;

    /**
     * @return bool
     */
    public function active(): bool;

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): DateTimeImmutable;
}

<?php
declare(strict_types = 1);

namespace App\ValueObject\Vendor;
final class VendorId
{
    public function __construct(private string $value)
    {
        if ($value === '') throw new \InvalidArgumentException('Empty VendorId');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

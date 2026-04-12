<?php

declare(strict_types=1);

namespace App\Command\Support;

use Symfony\Component\Console\Input\InputInterface;

use function is_scalar;
use function is_string;

final readonly class VendorRuntimeWindowInput
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public ?string $from,
        public ?string $to,
        public string $currency,
        public string $format,
    ) {}

    public static function fromInput(InputInterface $input): self
    {
        $tenantIdOption = $input->getOption('tenantId');
        $vendorIdOption = $input->getOption('vendorId');
        $fromOption = $input->getOption('from');
        $toOption = $input->getOption('to');
        $currencyOption = $input->getOption('currency');
        $formatOption = $input->getOption('format');

        return new self(
            tenantId: is_scalar($tenantIdOption) ? (string) $tenantIdOption : '',
            vendorId: is_scalar($vendorIdOption) ? (string) $vendorIdOption : '',
            from: is_string($fromOption) ? $fromOption : null,
            to: is_string($toOption) ? $toOption : null,
            currency: is_scalar($currencyOption) ? (string) $currencyOption : 'USD',
            format: is_scalar($formatOption) ? (string) $formatOption : 'text',
        );
    }

    public function hasRequiredScope(): bool
    {
        return '' !== $this->tenantId && '' !== $this->vendorId;
    }
}

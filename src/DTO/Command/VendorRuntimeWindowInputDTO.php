<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Command;

use Symfony\Component\Console\Input\InputInterface;

final readonly class VendorRuntimeWindowInputDTO
{
    public function __construct(
        public string $vendorId,
        public ?string $from,
        public ?string $to,
        public string $currency,
        public string $format,
    ) {
    }

    public static function fromInput(InputInterface $input): self
    {
        $vendorIdOption = $input->getOption('vendorId');
        $fromOption = $input->getOption('from');
        $toOption = $input->getOption('to');
        $currencyOption = $input->getOption('currency');
        $formatOption = $input->getOption('format');

        return new self(
            vendorId: \is_scalar($vendorIdOption) ? (string) $vendorIdOption : '',
            from: \is_string($fromOption) ? $fromOption : null,
            to: \is_string($toOption) ? $toOption : null,
            currency: \is_scalar($currencyOption) ? (string) $currencyOption : 'USD',
            format: \is_scalar($formatOption) ? (string) $formatOption : 'text',
        );
    }

    public function hasRequiredScope(): bool
    {
        return '' !== $this->vendorId;
    }
}

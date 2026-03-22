<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\Ops\VendorReleaseBaselineReaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vendor:release-baseline',
    description: 'Render a release-facing vendor baseline snapshot after a green runtime contour',
)]
final class VendorReleaseBaselineCommand extends Command
{
    public function __construct(private readonly VendorReleaseBaselineReaderInterface $releaseBaselineReader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tenantId', null, InputOption::VALUE_REQUIRED, 'Tenant ID')
            ->addOption('vendorId', null, InputOption::VALUE_REQUIRED, 'Vendor ID')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'Statement period start')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'Statement period end')
            ->addOption('currency', null, InputOption::VALUE_OPTIONAL, 'Currency', 'USD')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text')
            ->addOption('write', null, InputOption::VALUE_NONE, 'Write snapshot JSON to build/release')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Custom output path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenantId = (string) $input->getOption('tenantId');
        $vendorId = (string) $input->getOption('vendorId');
        $from = $input->getOption('from');
        $to = $input->getOption('to');
        $currency = (string) $input->getOption('currency');
        $format = (string) $input->getOption('format');

        if ('' === $tenantId || '' === $vendorId) {
            $output->writeln('<error>Both --tenantId and --vendorId are required.</error>');

            return Command::FAILURE;
        }

        $view = $this->releaseBaselineReader->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: \is_string($from) ? $from : null,
            to: \is_string($to) ? $to : null,
            currency: $currency,
        )->toArray();

        $json = (string) json_encode($view, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ((bool) $input->getOption('write')) {
            $path = $input->getOption('output');
            $outputPath = \is_string($path) && '' !== $path
                ? $path
                : dirname(__DIR__, 2).'/build/release/vendor-release-baseline.json';
            $dir = dirname($outputPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($outputPath, $json);
        }

        if ('json' === $format) {
            $output->writeln($json);

            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            'tenantId=%s vendorId=%s status=%s',
            $tenantId,
            $vendorId,
            $view['status'],
        ));
        $output->writeln(sprintf('issues=%d', count($view['issues'])));

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vendor:runtime-status',
    description: 'Render vendor runtime status across ownership, finance, statement delivery, and integrations',
)]
final class VendorRuntimeStatusCommand extends Command
{
    public function __construct(private readonly VendorRuntimeStatusViewBuilderInterface $runtimeStatusViewBuilder)
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
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text|json', 'text');
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

        $view = $this->runtimeStatusViewBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: \is_string($from) ? $from : null,
            to: \is_string($to) ? $to : null,
            currency: $currency,
        )->toArray();

        if ('json' === $format) {
            $output->writeln((string) json_encode($view, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $surfaceStatus = $view['surfaceStatus'];
        $output->writeln(sprintf('tenantId=%s vendorId=%s currency=%s', $tenantId, $vendorId, $currency));
        $output->writeln(sprintf(
            'ownership=%s finance=%s statementDelivery=%s externalIntegration=%s',
            $surfaceStatus['ownership'] ? 'ready' : 'missing',
            $surfaceStatus['finance'] ? 'ready' : 'missing',
            $surfaceStatus['statementDelivery'] ? 'ready' : 'missing',
            $surfaceStatus['externalIntegration'] ? 'ready' : 'missing',
        ));

        return Command::SUCCESS;
    }
}

<?php
declare(strict_types=1);

namespace App\Service\Vendor;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorLedgerBinding;
use App\Repository\Vendor\VendorLedgerBindingRepository;
use Doctrine\ORM\EntityManagerInterface;

final class LedgerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorLedgerBindingRepository $bindings
    ) {}

    public function createVendorAccount(Vendor $vendor): VendorLedgerBinding
    {
        $existing = $this->bindings->findOneBy(['vendor' => $vendor]);
        if ($existing) {
            return $existing;
        }
        $accountId = 'ACC-' . bin2hex(random_bytes(8));
        $binding = new VendorLedgerBinding($vendor, $accountId);
        $this->em->persist($binding);
        $this->em->flush();
        return $binding;
    }
}

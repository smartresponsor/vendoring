<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Service;


use App\RepositoryInterface\Vendor\Service\VendorServiceInterface;
use App\DTO\Vendor\VendorCreateDTO;
use App\DTO\Vendor\VendorUpdateDTO;
use App\Entity\Vendor\Vendor;
use App\Event\Vendor\VendorActivatedEvent;
use App\Event\Vendor\VendorCreatedEvent;
use App\Repository\Vendor\VendorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorService
    implements VendorServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly VendorRepository         $repository,
        private readonly EventDispatcherInterface $dispatcher
    )
    {
    }

    public function create(VendorCreateDTO $dto): Vendor
    {
        $vendor = new Vendor($dto->brandName, $dto->userId);
        $this->em->persist($vendor);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorCreatedEvent($vendor));

        return $vendor;
    }

    public function update(Vendor $vendor, VendorUpdateDTO $dto): Vendor
    {
        if ($dto->brandName !== null) {
            $vendor->rename($dto->brandName);
        }
        if ($dto->status === 'active') {
            $vendor->activate();
            $this->dispatcher->dispatch(new VendorActivatedEvent($vendor));
        } elseif ($dto->status === 'inactive') {
            $vendor->deactivate();
        }

        $this->em->flush();
        return $vendor;
    }
}

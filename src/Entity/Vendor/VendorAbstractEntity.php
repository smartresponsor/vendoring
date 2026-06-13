<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectIdentityEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectStateEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class VendorAbstractEntity
{
    use ObjectIdentityEmbeddableTrait;
    use ObjectAuditEmbeddableTrait;
    use ObjectStateEmbeddableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    public function __construct(?string $objectStatus = 'active')
    {
        $this->initializeObjectIdentity();
        $this->initializeObjectAudit();
        $this->initializeObjectState(objectStatus: $objectStatus);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->getObjectStatus() ?? 'active';
    }

    public function setStatus(string $status): self
    {
        $this->setObjectStatus($status);
        $this->touchObject();

        return $this;
    }
}

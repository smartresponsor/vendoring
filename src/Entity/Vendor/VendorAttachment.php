<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorAttachmentRepository')]
#[ORM\Table(name: 'vendor_attachment')]
class VendorAttachment
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 160)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $filePath;

    #[ORM\Column(length: 48, nullable: true)]
    private ?string $category = null;

    public function __construct(Vendor $vendor, string $title, string $filePath, ?string $category = null)
    {
        $this->vendor = $vendor;
        $this->title = $title;
        $this->filePath = $filePath;
        $this->category = $category;
    }
}

<?php declare(strict_types=1);
namespace App\ServiceInterface\Vendor\Port\Repository; use App\Entity\Vendor\Vendor; use App\ValueObject\Vendor\VendorId; interface VendorRepositoryPort{public function get(VendorId $id):?Vendor; public function listActive():array; public function save(Vendor $v):void; public function remove(VendorId $id):void;}

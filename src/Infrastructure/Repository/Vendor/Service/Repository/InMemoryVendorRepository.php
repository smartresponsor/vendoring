<?php declare(strict_types=1);
namespace App\Infrastructure\Repository\Vendor\Service\Repository;
use App\RepositoryInterface\Vendor\Service\Repository\InMemoryVendorRepositoryInterface;
use SmartResponsor\Vendor\Port\Repository\VendorRepositoryPort; use SmartResponsor\Vendor\Entity\Vendor\Vendor; use App\ValueObject\Vendor\VendorId; final class InMemoryVendorRepository implements VendorRepositoryPort, InMemoryVendorRepositoryInterface {private array $m=[]; public function get(VendorId $id):?Vendor{return $this->m[(string)$id]??null;} public function listActive():array{return array_values(array_filter($this->m,fn($v)=>$v->active()));} public function save(Vendor $v):void{$this->m[$v->id()]=$v;} public function remove(VendorId $id):void{unset($this->m[(string)$id]);}}

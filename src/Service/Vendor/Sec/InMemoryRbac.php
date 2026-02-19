<?php declare(strict_types=1);
namespace App\Service\Vendor\Sec; use SmartResponsor\Vendor\Port\Sec\RbacPort; final class InMemoryRbac implements RbacPort{public function __construct(private array $matrix){} public function can(string $actor,string $role,string $res,string $op):bool{ return in_array($op, $this->matrix[$role][$res]??[], true);} }

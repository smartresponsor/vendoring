<?php declare(strict_types=1);
namespace App\ServiceInterface\Vendor\Port\Sec; interface RbacPort{public function can(string $actor,string $role,string $resource,string $op): bool;}
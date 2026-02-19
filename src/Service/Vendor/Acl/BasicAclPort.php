<?php declare(strict_types=1);
namespace App\Service\Vendor\Acl; use SmartResponsor\Vendor\Port\Acl\AclPort; final class BasicAclPort implements AclPort{public function __construct(private array $rules){ } public function allow(string $actor,string $action):bool{ return in_array($action,$this->rules[$actor]??[],true);} }

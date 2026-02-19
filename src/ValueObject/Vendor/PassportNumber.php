<?php declare(strict_types=1);
namespace App\ValueObject\Vendor; final class PassportNumber{public function __construct(private string $v){if(!preg_match('/^[A-Z0-9]{6,15}$/',$v)) throw new \InvalidArgumentException('Bad passport');} public function __toString():string{return $this->v;}}

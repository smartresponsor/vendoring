<?php
declare(strict_types=1);

namespace App\Bridge\Vendor;

use Doctrine\ORM\EntityManagerInterface;

final class Transaction
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    /** @template T @param callable():T $fn @return T */
    public function run(callable $fn)
    {
        $this->em->beginTransaction();
        try {
            $res = $fn();
            $this->em->commit();
            return $res;
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}

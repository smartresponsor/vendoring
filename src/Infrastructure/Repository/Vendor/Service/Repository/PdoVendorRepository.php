<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Service\Repository;

use App\RepositoryInterface\Vendor\Service\Repository\PdoVendorRepositoryInterface;
use PDO;
use SmartResponsor\Vendor\Port\Repository\VendorRepositoryPort;
use SmartResponsor\Vendor\Entity\Vendor\Vendor;
use App\ValueObject\Vendor\VendorId;

final class PdoVendorRepository implements VendorRepositoryPort, PdoVendorRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function get(VendorId $id): ?Vendor
    {
        $st = $this->pdo->prepare('SELECT id,name,active FROM vendor WHERE id = :id LIMIT 1');
        $st->execute([':id' => (string)$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return new Vendor($row['id'], $row['name'], (bool)$row['active']);
    }

    public function listActive(): array
    {
        $rows = $this->pdo->query('SELECT id,name,active FROM vendor WHERE active = 1')->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Vendor($r['id'], $r['name'], (bool)$r['active']), $rows);
    }

    public function save(Vendor $vendor): void
    {
        $this->pdo->prepare('INSERT INTO vendor(id,name,active) VALUES(:id,:name,:active)
            ON CONFLICT(id) DO UPDATE SET name=:name_u, active=:active_u')->execute([
            ':id' => $vendor->id(), ':name' => $vendor->name(), ':active' => $vendor->active() ? 1 : 0,
            ':name_u' => $vendor->name(), ':active_u' => $vendor->active() ? 1 : 0,
        ]);
    }

    public function remove(VendorId $id): void
    {
        $this->pdo->prepare('DELETE FROM vendor WHERE id=:id')->execute([':id' => (string)$id]);
    }
}

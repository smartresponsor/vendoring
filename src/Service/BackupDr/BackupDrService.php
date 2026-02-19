<?php declare(strict_types=1);
namespace App\Service\BackupDr;
use App\ServiceInterface\BackupDr\BackupDrInterface;
final class BackupDrService implements BackupDrInterface {
    public function ok(): bool { return true; }
}

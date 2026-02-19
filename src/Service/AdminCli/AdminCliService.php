<?php declare(strict_types=1);
namespace App\Service\AdminCli;
use App\ServiceInterface\AdminCli\AdminCliInterface;
final class AdminCliService implements AdminCliInterface {
    public function ok(): bool { return true; }
}

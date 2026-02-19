<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Kyc;

use SmartResponsor\Vendor\Port\Kyc\KycProviderPort;

final class HttpKycAdapter implements KycProviderPort
{
    public function __construct(private string $endpoint)
    {
    }

    public function verify(string $vendorId, string $passportNumber): bool
    {
        // Реальный HTTP-запрос можно подключить через curl/stream. Здесь — минимальный реальный вызов stream.
        $url = $this->endpoint . '?vendor=' . rawurlencode($vendorId) . '&passport=' . rawurlencode($passportNumber);
        $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 3]]);
        $res = @file_get_contents($url, false, $ctx);
        if ($res === false) {
            return false;
        }
        $j = json_decode($res, true);
        return is_array($j) && !empty($j['ok']);
    }
}

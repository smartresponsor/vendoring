<?php declare(strict_types=1);
namespace App\Service\I18nLocalization;
use App\ServiceInterface\I18nLocalization\I18nLocalizationInterface;
final class I18nLocalizationService implements I18nLocalizationInterface {
    public function ok(): bool { return true; }
}

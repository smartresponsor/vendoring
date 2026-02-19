<?php declare(strict_types=1);
namespace App\Service\SearchRelevance;
use App\ServiceInterface\SearchRelevance\SearchRelevanceInterface;
final class SearchRelevanceService implements SearchRelevanceInterface {
    public function ok(): bool { return true; }
}

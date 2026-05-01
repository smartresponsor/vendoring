<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Search;

use App\Vendoring\ServiceInterface\Search\VendorTfidfSearchServiceInterface;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorTfidfSearchService implements VendorTfidfSearchServiceInterface
{
    /** @var array<int, array{tokens: array<string, int>, tfidf: array<string, float>}> */
    private array $documents = [];

    /** @var array<string, int> */
    private array $documentFrequencies = [];

    private int $documentCount = 0;

    /** @return list<string> */
    private function tokenize(string $sourceText): array
    {
        $lowercaseText = mb_strtolower($sourceText);
        $normalizedText = preg_replace('/[^a-z0-9\p{Cyrillic}\s]+/u', ' ', $lowercaseText);
        $preparedText = is_string($normalizedText) ? $normalizedText : $lowercaseText;
        $tokens = preg_split('/\s+/u', trim($preparedText)) ?: [];

        return array_values(array_filter($tokens, static fn(string $token): bool => '' !== $token));
    }

    public function addDocument(string $text): int
    {
        $tokens = $this->tokenize($text);
        $tokenFrequencies = [];

        foreach ($tokens as $token) {
            $tokenFrequencies[$token] = ($tokenFrequencies[$token] ?? 0) + 1;
        }

        foreach (array_keys($tokenFrequencies) as $token) {
            $this->documentFrequencies[$token] = ($this->documentFrequencies[$token] ?? 0) + 1;
        }

        $documentId = $this->documentCount;
        $this->documents[$documentId] = ['tokens' => $tokenFrequencies, 'tfidf' => []];
        ++$this->documentCount;

        return $documentId;
    }

    public function finalize(): void
    {
        foreach ($this->documents as $documentId => $document) {
            $tfidfVector = $this->buildNormalizedVector($document['tokens']);
            $this->documents[$documentId]['tfidf'] = $tfidfVector;
        }
    }

    public function search(string $query, int $limit = 10): array
    {
        $queryTokens = $this->tokenize($query);
        $queryFrequencies = [];

        foreach ($queryTokens as $token) {
            $queryFrequencies[$token] = ($queryFrequencies[$token] ?? 0) + 1;
        }

        $queryVector = $this->buildNormalizedVector($queryFrequencies);
        $scores = [];

        foreach ($this->documents as $documentId => $document) {
            $dotProduct = 0.0;

            foreach ($queryVector as $token => $queryWeight) {
                $dotProduct += ($document['tfidf'][$token] ?? 0.0) * $queryWeight;
            }

            if ($dotProduct > 0.0) {
                $scores[$documentId] = $dotProduct;
            }
        }

        arsort($scores);

        return array_slice(
            array_map(
                fn(int $documentId): array => ['id' => $documentId, 'score' => $scores[$documentId]],
                array_keys($scores),
            ),
            0,
            $limit,
        );
    }

    /**
     * @param array<string, int> $tokenFrequencies
     * @return array<string, float>
     */
    private function buildNormalizedVector(array $tokenFrequencies): array
    {
        if ([] === $tokenFrequencies) {
            return [];
        }

        $tfidfVector = [];
        $normalizationFactor = 0.0;
        $maxFrequency = max($tokenFrequencies);

        foreach ($tokenFrequencies as $token => $frequency) {
            $termFrequency = 0.5 + 0.5 * ($frequency / $maxFrequency);
            $inverseDocumentFrequency = log(($this->documentCount + 1) / (($this->documentFrequencies[$token] ?? 1) + 1)) + 1.0;
            $tokenWeight = $termFrequency * $inverseDocumentFrequency;
            $tfidfVector[$token] = $tokenWeight;
            $normalizationFactor += $tokenWeight * $tokenWeight;
        }

        $normalizationFactor = sqrt($normalizationFactor) ?: 1.0;

        foreach ($tfidfVector as $token => $tokenWeight) {
            $tfidfVector[$token] = $tokenWeight / $normalizationFactor;
        }

        return $tfidfVector;
    }
}

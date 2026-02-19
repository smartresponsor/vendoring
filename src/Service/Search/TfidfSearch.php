<?php
declare(strict_types = 1);

namespace App\Service\Search;
final class TfidfSearch
{
    /** @var array<int,array{tokens:array<string,int>, tfidf:array<string,float>}> */
    private array $docs = [];
    /** @var array<string,int> */
    private array $df = [];
    private int $N = 0;

    private function tokenize(string $s): array
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^a-z0-9\p{Cyrillic}\s]+/u', ' ', $s);
        $t = preg_split('/\s+/u', trim($s)) ?: [];
        return array_values(array_filter($t, fn($x) => $x !== ''));
    }

    public function addDocument(string $text): int
    {
        $tokens = $this->tokenize($text);
        $freq = [];
        foreach ($tokens as $tok) {
            $freq[$tok] = ($freq[$tok] ?? 0) + 1;
        }
        foreach (array_keys($freq) as $tok) {
            $this->df[$tok] = ($this->df[$tok] ?? 0) + 1;
        }
        $id = $this->N;
        $this->docs[$id] = ['tokens' => $freq, 'tfidf' => []];
        $this->N++;
        return $id;
    }

    public function finalize(): void
    {
        foreach ($this->docs as $id => $_) {
            $tfidf = [];
            $norm = 0.0;
            $maxf = max($_['tokens']);
            foreach ($_['tokens'] as $tok => $f) {
                $tf = 0.5 + 0.5 * ($f / $maxf);
                $idf = log(($this->N + 1) / (($this->df[$tok] ?? 1) + 1)) + 1.0;
                $w = $tf * $idf;
                $tfidf[$tok] = $w;
                $norm += $w * $w;
            }
            $norm = sqrt($norm) ?: 1.0;
            foreach ($tfidf as $tok => $w) {
                $tfidf[$tok] = $w / $norm;
            }
            $this->docs[$id]['tfidf'] = $tfidf;
        }
    }

    public function search(string $query, int $limit = 10): array
    {
        $qt = $this->tokenize($query);
        $qfreq = [];
        foreach ($qt as $t) {
            $qfreq[$t] = ($qfreq[$t] ?? 0) + 1;
        }
        $qtfidf = [];
        $norm = 0.0;
        $maxf = empty($qfreq) ? 1 : max($qfreq);
        foreach ($qfreq as $tok => $f) {
            $tf = 0.5 + 0.5 * ($f / $maxf);
            $idf = log(($this->N + 1) / (($this->df[$tok] ?? 1) + 1)) + 1.0;
            $w = $tf * $idf;
            $qtfidf[$tok] = $w;
            $norm += $w * $w;
        }
        $norm = sqrt($norm) ?: 1.0;
        foreach ($qtfidf as $k => $w) {
            $qtfidf[$k] = $w / $norm;
        }
        $scores = [];
        foreach ($this->docs as $id => $doc) {
            $dot = 0.0;
            foreach ($qtfidf as $tok => $qw) {
                $dot += ($doc['tfidf'][$tok] ?? 0.0) * $qw;
            }
            if ($dot > 0) $scores[$id] = $dot;
        }
        arsort($scores);
        return array_slice(array_map(fn($id) => ['id' => $id, 'score' => $scores[$id]], array_keys($scores)), 0, $limit);
    }
}

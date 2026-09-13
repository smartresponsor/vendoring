<?php

declare(strict_types=1);

namespace Gating\Gate\Rule\Canon;

use Gating\Gate\Contract\RuleContext;
use Gating\Gate\Contract\RuleResult;

/**
 * Evaluates Canon042 functional/behavioral/UI coverage evidence.
 */
final class Canon042BehavioralUiCoverageRule extends AbstractCanonRule
{
    private const array THRESHOLDS = [
        'functional' => 80.0,
        'behavioral' => 80.0,
        'ui' => 70.0,
        'critical' => 100.0,
    ];

    private const array HIGH_DEBT = [
        'functional' => 50.0,
        'behavioral' => 50.0,
        'ui' => 40.0,
        'critical' => 100.0,
    ];

    public function id(): string
    {
        return 'canon.042.behavioral_ui_coverage';
    }

    public function check(RuleContext $context): RuleResult
    {
        if (!$this->isSymfonyApplication($context)) {
            return $this->result('skipped', 'No standalone Symfony application runtime was detected.');
        }

        $path = $context->targetPath.'/var/coverage/behavioral-ui.json';
        if (!is_file($path)) {
            return $this->result(
                'warning',
                'Behavioral/UI coverage evidence is missing.',
                ['Generate var/coverage/behavioral-ui.json from the repository test-surface coverage workflow.'],
                'warning',
            );
        }

        $evidence = json_decode((string) file_get_contents($path), true);
        if (!is_array($evidence)) {
            return $this->invalid('Behavioral/UI coverage evidence is not valid JSON.');
        }

        $percentages = [];
        $details = [];
        foreach (self::THRESHOLDS as $dimension => $threshold) {
            $metric = $evidence[$dimension] ?? null;
            if (!is_array($metric) || !is_int($metric['covered'] ?? null) || !is_int($metric['total'] ?? null)) {
                return $this->invalid('Missing integer covered/total counters for '.$dimension.'.');
            }

            $covered = $metric['covered'];
            $total = $metric['total'];
            if ($covered < 0 || $total < 0 || $covered > $total) {
                return $this->invalid('Invalid covered/total counters for '.$dimension.'.');
            }

            $percentages[$dimension] = 0 === $total ? 100.0 : 100.0 * $covered / $total;
            $details[] = sprintf('%s %d/%d (%.1f%%; target %.0f%%)', $dimension, $covered, $total, $percentages[$dimension], $threshold);
        }

        if ($this->isStale($context, $path)) {
            return $this->result('warning', 'Behavioral/UI coverage evidence is stale relative to application source/UI surfaces.', $details, 'warning');
        }

        $highDebt = false;
        foreach (self::HIGH_DEBT as $dimension => $threshold) {
            if ($percentages[$dimension] < $threshold) {
                $highDebt = true;
                break;
            }
        }

        $passes = true;
        foreach (self::THRESHOLDS as $dimension => $threshold) {
            if ($percentages[$dimension] < $threshold) {
                $passes = false;
                break;
            }
        }

        $summary = 'Behavioral/UI coverage: '.implode('; ', $details).($highDebt ? '; HIGH_BEHAVIORAL_TEST_DEBT' : '').'.';
        if ($passes) {
            return $this->result('passed', $summary);
        }

        if ($highDebt) {
            array_unshift($details, 'HIGH_BEHAVIORAL_TEST_DEBT: repository is eligible for behavioral/UI test remediation.');
        }

        return $this->result('warning', $summary, $details, 'warning');
    }

    private function invalid(string $message): RuleResult
    {
        return $this->result('warning', $message, [], 'warning');
    }

    private function isSymfonyApplication(RuleContext $context): bool
    {
        $composerPath = $context->targetPath.'/composer.json';
        if (is_file($composerPath)) {
            $composer = json_decode((string) file_get_contents($composerPath), true);
            if (is_array($composer)) {
                $require = is_array($composer['require'] ?? null) ? $composer['require'] : [];
                if (array_key_exists('symfony/framework-bundle', $require)) {
                    return true;
                }
            }
        }

        return is_file($context->targetPath.'/bin/console')
            && is_file($context->targetPath.'/config/bundles.php')
            && (is_file($context->targetPath.'/app/Kernel.php') || is_file($context->targetPath.'/src/Kernel.php'));
    }

    private function isStale(RuleContext $context, string $evidencePath): bool
    {
        $evidenceTime = filemtime($evidencePath);
        if (false === $evidenceTime) {
            return true;
        }

        foreach (['src', 'templates', 'assets'] as $directory) {
            $root = $context->targetPath.'/'.$directory;
            if (!is_dir($root)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile() && $file->getMTime() > $evidenceTime) {
                    return true;
                }
            }
        }

        return false;
    }
}

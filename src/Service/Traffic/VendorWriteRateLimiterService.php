<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Traffic;

use App\Vendoring\ServiceInterface\Traffic\VendorWriteRateLimiterServiceInterface;
use App\Vendoring\ValueObject\Traffic\VendorWriteRateLimitDecisionValueObject;

/**
 * Host-injectable write limiter with a private, per-runtime sliding-window store.
 * The local store serializes concurrent workers using flock; a multi-host
 * deployment should inject a shared backend. Storage failures deny writes.
 *
 * @phpstan-type BackendDecision array{allowed: bool, remaining: int, retryAfterSeconds: int}
 */
final class VendorWriteRateLimiterService implements VendorWriteRateLimiterServiceInterface
{
    /** @param callable(string $bucketId, int $limit): BackendDecision|null $backendFactory */
    public function __construct(
        private readonly mixed $backendFactory = null,
        private readonly ?string $stateDir = null,
    ) {
    }

    public function consume(string $scope, string $actorKey, int $limit, int $windowSeconds): VendorWriteRateLimitDecisionValueObject
    {
        $scope = trim($scope);
        $actorKey = trim($actorKey);

        if ($limit < 1 || $windowSeconds < 1 || '' === $scope || '' === $actorKey) {
            return new VendorWriteRateLimitDecisionValueObject(true, max(1, $limit), max(0, $limit - 1), 0);
        }

        if (null !== $this->backendFactory) {
            try {
                $decision = ($this->backendFactory)($scope.'.'.$actorKey, $limit);

                return new VendorWriteRateLimitDecisionValueObject(
                    $decision['allowed'], $limit, $decision['remaining'], $decision['retryAfterSeconds'],
                );
            } catch (\Throwable) {
                return $this->unavailable($limit, $windowSeconds);
            }
        }

        if (null === $this->stateDir || '' === trim($this->stateDir)) {
            return $this->unavailable($limit, $windowSeconds);
        }

        return $this->consumeLocal($scope, $actorKey, $limit, $windowSeconds);
    }

    private function consumeLocal(string $scope, string $actorKey, int $limit, int $windowSeconds): VendorWriteRateLimitDecisionValueObject
    {
        $denied = $this->unavailable($limit, $windowSeconds);
        $dir = $this->stateDir;
        if (null === $dir || (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir))) {
            return $denied;
        }
        clearstatcache(true, $dir);
        $mode = @fileperms($dir);
        if (is_link($dir) || false === $mode || (0 !== ($mode & 0077) && 'Windows' !== PHP_OS_FAMILY)) {
            return $denied;
        }

        $path = $dir.DIRECTORY_SEPARATOR.hash('sha256', json_encode([$scope, $actorKey], JSON_THROW_ON_ERROR)).'.json';
        $lockPath = $path.'.lock';
        if (is_link($lockPath) || (file_exists($lockPath) && !is_file($lockPath))) {
            return $denied;
        }
        $handle = @fopen($lockPath, 'c+');
        if (false === $handle) {
            return $denied;
        }

        try {
            if (!@flock($handle, LOCK_EX)) {
                return $denied;
            }
            clearstatcache(true, $lockPath);
            $opened = fstat($handle);
            $named = @lstat($lockPath);
            if (false === $opened || false === $named || $opened['ino'] !== $named['ino'] || $opened['dev'] !== $named['dev']
                || 1 !== $opened['nlink'] || 0100000 !== ($opened['mode'] & 0170000) || !@chmod($lockPath, 0600)) {
                return $denied;
            }
            clearstatcache(true, $path);
            $raw = '';
            if (is_link($path)) {
                return $denied;
            }
            if (file_exists($path)) {
                $stat = @lstat($path);
                if (false === $stat || 1 !== $stat['nlink'] || 0100000 !== ($stat['mode'] & 0170000)) {
                    return $denied;
                }
                $raw = @file_get_contents($path, false, null, 0, 1048577);
            }
            if (false === $raw || strlen($raw) > 1048576) {
                return $denied;
            }
            $history = '' === $raw ? [] : json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
            if (!is_array($history) || !array_is_list($history)) {
                return $denied;
            }
            $now = time();
            $retained = [];
            foreach ($history as $timestamp) {
                if (!is_int($timestamp) || $timestamp > $now) {
                    return $denied;
                }
                if ($timestamp > $now - $windowSeconds) {
                    $retained[] = $timestamp;
                }
            }
            $allowed = count($retained) < $limit;
            if ($allowed) {
                $retained[] = $now;
            }
            $encoded = json_encode($retained, JSON_THROW_ON_ERROR);
            $tmp = @tempnam($dir, '.rate-');
            if (false === $tmp) {
                return $denied;
            }
            try {
                if (dirname($tmp) !== $dir || !@chmod($tmp, 0600)
                    || strlen($encoded) !== @file_put_contents($tmp, $encoded)
                    || !@rename($tmp, $path)) {
                    return $denied;
                }
            } finally {
                if (is_file($tmp)) {
                    @unlink($tmp);
                }
            }

            return new VendorWriteRateLimitDecisionValueObject(
                $allowed,
                $limit,
                max(0, $limit - count($retained)),
                $allowed ? 0 : max(1, ([] === $retained ? $now : min($retained)) + $windowSeconds - $now),
            );
        } catch (\Throwable) {
            return $denied;
        } finally {
            @flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function unavailable(int $limit, int $windowSeconds): VendorWriteRateLimitDecisionValueObject
    {
        return new VendorWriteRateLimitDecisionValueObject(false, $limit, 0, max(1, $windowSeconds));
    }
}

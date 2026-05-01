<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Traffic;

use App\Vendoring\ServiceInterface\Traffic\VendorWriteRateLimiterServiceInterface;
use App\Vendoring\ValueObject\Traffic\VendorWriteRateLimitDecisionValueObject;
use JsonException;

/**
 * File-backed write rate limiter for low-complexity runtime environments.
 *
 * The limiter persists timestamp history in the local filesystem and produces immutable
 * rate-limit decisions for one scope/actor pair.
 */
final class VendorWriteRateLimiterService implements VendorWriteRateLimiterServiceInterface
{
    /**
     * Consume one slot from the write-rate limit bucket for the given scope and actor.
     */
    public function consume(string $scope, string $actorKey, int $limit, int $windowSeconds): VendorWriteRateLimitDecisionValueObject
    {
        $normalizedScope = trim($scope);
        $normalizedActorKey = trim($actorKey);

        if ($limit < 1 || $windowSeconds < 1 || '' === $normalizedScope || '' === $normalizedActorKey) {
            return new VendorWriteRateLimitDecisionValueObject(true, max(1, $limit), max(0, $limit - 1), 0);
        }

        $path = $this->storagePath($normalizedScope, $normalizedActorKey);
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            return new VendorWriteRateLimitDecisionValueObject(true, $limit, max(0, $limit - 1), 0);
        }

        $handle = fopen($path, 'c+');
        if (false === $handle) {
            return new VendorWriteRateLimitDecisionValueObject(true, $limit, max(0, $limit - 1), 0);
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return new VendorWriteRateLimitDecisionValueObject(true, $limit, max(0, $limit - 1), 0);
            }

            $now = time();
            $history = $this->readTimestamps($handle);
            $threshold = $now - $windowSeconds;
            $history = array_values(array_filter($history, static fn(int $timestamp): bool => $timestamp > $threshold));

            if (count($history) >= $limit) {
                $oldest = min($history);
                $retryAfter = max(1, ($oldest + $windowSeconds) - $now);

                $this->writeTimestamps($handle, $history);

                return new VendorWriteRateLimitDecisionValueObject(false, $limit, 0, $retryAfter);
            }

            $history[] = $now;
            $this->writeTimestamps($handle, $history);

            return new VendorWriteRateLimitDecisionValueObject(true, $limit, max(0, $limit - count($history)), 0);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Resolve the storage path for one rate-limit bucket.
     */
    private function storagePath(string $scope, string $actorKey): string
    {
        $hash = sha1($scope . '|' . $actorKey);

        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vendoring_rate_limit' . DIRECTORY_SEPARATOR . $hash . '.json';
    }

    /**
     * Read persisted timestamps from one file handle.
     *
     * @return list<int> Historical timestamps still associated with the bucket file.
     */
    private function readTimestamps(mixed $handle): array
    {
        if (!is_resource($handle)) {
            return [];
        }

        rewind($handle);
        $contents = stream_get_contents($handle);

        if (!is_string($contents) || '' === trim($contents)) {
            return [];
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return [];
        }

        $timestamps = [];
        foreach ($decoded as $value) {
            if (is_int($value)) {
                $timestamps[] = $value;
            }
        }

        return $timestamps;
    }

    /**
     * Persist the normalized timestamp history back into the bucket file.
     *
     * @param mixed $handle
     * @param list<int> $timestamps Timestamp history to persist.
     * @throws JsonException
     */
    private function writeTimestamps(mixed $handle, array $timestamps): void
    {
        if (!is_resource($handle)) {
            return;
        }

        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($timestamps, JSON_THROW_ON_ERROR));
        fflush($handle);
    }
}

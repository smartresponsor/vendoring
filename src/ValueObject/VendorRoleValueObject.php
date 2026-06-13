<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject;

/**
 * Canonical RBAC role registry for human/operator access within the vendor bounded context.
 *
 * Machine access stays governed by API-key permissions. This value object defines only
 * vendor-local human roles used by assignment, ownership, and authorization decisions.
 */
final class VendorRoleValueObject
{
    public const string OWNER = 'owner';
    public const string OPERATOR = 'operator';
    public const string FINANCE = 'finance';
    public const string VIEWER = 'viewer';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::OWNER,
            self::OPERATOR,
            self::FINANCE,
            self::VIEWER,
        ];
    }

    public static function normalize(string $role): string
    {
        return strtolower(trim($role));
    }

    public static function isValid(string $role): bool
    {
        return in_array(self::normalize($role), self::all(), true);
    }
}

<?php

declare(strict_types=1);

namespace App\Core\Tenant;

use App\Models\Organization;
use RuntimeException;

class TenantContext
{
    protected static ?Organization $currentOrganization = null;

    public static function set(Organization $organization): void
    {
        self::$currentOrganization = $organization;
    }

    public static function get(): ?Organization
    {
        return self::$currentOrganization;
    }

    public static function id(): ?int
    {
        return self::$currentOrganization?->id;
    }

    public static function requireId(): int
    {
        $id = self::id();

        if ($id === null) {
            throw new RuntimeException('Tenant context has not been initialized.');
        }

        return $id;
    }

    public static function clear(): void
    {
        self::$currentOrganization = null;
    }
}

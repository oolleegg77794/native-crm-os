<?php

declare(strict_types=1);

namespace App\Core\Permissions;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RoleHierarchyService
{
    /**
     * Get all subordinate user IDs for a given user based on the Role tree.
     *
     * @return list<int>
     */
    public function getSubordinateUserIds(User $user, bool $includeSelf = true): array
    {
        if ($user->role_id === null) {
            return $includeSelf ? [$user->id] : [];
        }

        $cacheKey = "role_subordinates_user_{$user->id}_include_" . ($includeSelf ? '1' : '0');

        return Cache::remember($cacheKey, 600, function () use ($user, $includeSelf) {
            $role = $user->role;
            if ($role === null) {
                return $includeSelf ? [$user->id] : [];
            }

            $descendantRoleIds = $role->descendantIds(false);

            $userIds = User::where('organization_id', $user->organization_id)
                ->whereIn('role_id', $descendantRoleIds)
                ->pluck('id')
                ->all();

            if ($includeSelf) {
                array_unshift($userIds, $user->id);
            }

            return array_values(array_unique($userIds));
        });
    }

    /**
     * Determine if $actor has role seniority over $target.
     */
    public function isSuperiorTo(User $actor, User $target): bool
    {
        if ($actor->organization_id !== $target->organization_id) {
            return false;
        }

        if ($actor->isAdmin()) {
            return true;
        }

        if ($actor->id === $target->id) {
            return true;
        }

        if ($actor->role_id === null || $target->role_id === null) {
            return false;
        }

        $actorRole = $actor->role;
        $targetRole = $target->role;

        if ($actorRole === null || $targetRole === null) {
            return false;
        }

        return $actorRole->isAncestorOf($targetRole);
    }
}

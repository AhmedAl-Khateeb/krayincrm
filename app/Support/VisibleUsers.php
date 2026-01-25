<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class VisibleUsers
{
    public static function ids(): ?array
    {
        $u = auth()->user();

        if (!$u) {
            return [];
        }

        // ✅ Admin / Global permission: no filtering
        $rolePermission = $u->role?->permission_type; // 'all' مثلاً
        $viewPermission = $u->view_permission ?? 'self'; // self | group | global

        if ($rolePermission === 'all' || $viewPermission === 'global') {
            return null;
        }

        // ✅ Self only
        if ($viewPermission !== 'group') {
            return [(int) $u->id];
        }

        // ✅ Group
        $groupIds = DB::table('user_groups')
            ->where('user_id', $u->id)
            ->pluck('group_id')
            ->filter()              // يشيل null
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($groupIds)) {
            return [(int) $u->id];
        }

        return DB::table('user_groups')
            ->whereIn('group_id', $groupIds)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}

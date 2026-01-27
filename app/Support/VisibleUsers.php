<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class VisibleUsers
{
    public static function ids(): ?array
    {
        $u = auth()->guard('user')->user();

        if (!$u) {
            return []; // مش لوجين أصلا
        }

        $rolePermission = $u->role?->permission_type; // all | custom
        $viewPermission = $u->view_permission ?? 'self'; // self | group | global

        // Admin/global => بدون فلترة
        if ($rolePermission === 'all' || $viewPermission === 'global') {
            return null;
        }

        if ($viewPermission !== 'group') {
            return [(int) $u->id];
        }

        $groupIds = DB::table('user_groups')
            ->where('user_id', $u->id)
            ->pluck('group_id')
            ->filter()
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

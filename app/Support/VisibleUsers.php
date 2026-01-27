<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisibleUsers
{
    public static function ids(): ?array
    {
        $u = auth()->guard('user')->user();

        // ✅ DEBUG LOG (ضيفه هنا)
        Log::info('VISIBLE USERS DEBUG', [
            'guard_user_id' => auth()->guard('user')->id(),
            'default_guard_id' => auth()->id(),
            'view_permission' => $u?->view_permission,
            'role_permission_type' => $u?->role?->permission_type,
        ]);

        if (!$u) {
            return [];
        }

        $rolePermission = $u->role?->permission_type;
        $viewPermission = $u->view_permission ?? 'self';

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

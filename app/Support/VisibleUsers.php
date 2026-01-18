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

        if (($u->role?->permission_type === 'all') || ($u->view_permission === 'global')) {
            return null;
        }

        if (($u->view_permission ?? 'self') !== 'group') {
            return [$u->id];
        }

        $groupIds = DB::table('user_groups')->where('user_id', $u->id)->pluck('group_id');
        if ($groupIds->isEmpty()) {
            return [$u->id];
        }

        return DB::table('user_groups')
            ->whereIn('group_id', $groupIds)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }
}

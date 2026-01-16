<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Webkul\Activity\Models\Activity;

class NotificationFeedController extends Controller
{
    /**
     * Return latest lead notes as "notification feed"
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->guard('user')->user();

        // ✅ لو مش logged in لأي سبب
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $limit = (int) $request->query('limit', 10);
        if ($limit < 1)  $limit = 10;
        if ($limit > 50) $limit = 50;

        $seenKey    = "admin:notif_feed:last_seen:{$user->id}";
        $lastSeenTs = (int) Cache::get($seenKey, 0);

        // ✅ هات الـ notes مباشرة من الموديل (بدون Repository)
        $notes = Activity::query()
            ->where('type', 'note')
            ->with(['leads:id,title'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $items = $notes->map(function ($a) use ($lastSeenTs) {
            $lead = $a->leads->first();

            $createdTs = $a->created_at ? $a->created_at->timestamp : 0;
            $isNew = $createdTs > $lastSeenTs;

            return [
                'id'         => $a->id,
                'is_new'     => $isNew,
                'created_at' => $a->created_at?->toDateTimeString(),
                'comment'    => (string) ($a->comment ?? ''),
                'lead'       => $lead ? [
                    'id'    => $lead->id,
                    'title' => $lead->title ?: ('Lead #' . $lead->id),
                ] : null,
            ];
        })->values();

        $unseen = $items->where('is_new', true)->count();

        return response()->json([
            'unseen' => $unseen,
            'items'  => $items,
        ]);
    }

    /**
     * Mark feed as seen
     */
    public function markSeen(): JsonResponse
    {
        $user = auth()->guard('user')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $seenKey = "admin:notif_feed:last_seen:{$user->id}";
        Cache::put($seenKey, now()->timestamp, 60 * 60 * 24 * 30);

        return response()->json(['ok' => true]);
    }
}


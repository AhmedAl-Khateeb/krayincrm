<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->guard('user')->user();

        $limit = (int) $request->query('limit', 10);
        if ($limit < 1) {
            $limit = 10;
        }
        if ($limit > 50) {
            $limit = 50;
        }

        // unread + latest
        $unreadCount = $user->unreadNotifications()->count();

        $items = $user->unreadNotifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($n) {
                $data = is_array($n->data) ? $n->data : json_decode($n->data, true);

                return [
                    'id' => $n->id,
                    'is_read' => !is_null($n->read_at),
                    'created_at' => optional($n->created_at)->toDateTimeString(),
                    'title' => $data['title'] ?? ($data['type'] ?? 'Notification'),
                    'body' => $data['body'] ?? ($data['message'] ?? ($data['note'] ?? '')),
                    'url' => $data['url'] ?? null,

                    // useful extras
                    'lead_id' => $data['lead_id'] ?? null,
                    'activity_id' => $data['activity_id'] ?? null,
                ];
            })->values();

        return response()->json([
            'unread_count' => $unreadCount,
            'items' => $items,
        ]);
    }

    public function readAll(): JsonResponse
    {
        $user = auth()->guard('user')->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function read(Request $request, string $id): JsonResponse
    {
        $user = auth()->guard('user')->user();

        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return response()->json(['ok' => true]);
    }
}

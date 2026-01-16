<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'limit'       => 'nullable|integer|min:1|max:100',
            'unread_only' => 'nullable',
        ]);

        $user = $request->user('user'); // admin guard
        $limit = (int) ($request->input('limit', 20));
        $unreadOnly = filter_var($request->input('unread_only', false), FILTER_VALIDATE_BOOLEAN);

        $query = $user->notifications()->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        $items = $query->limit($limit)->get()->map(function ($n) {
            return [
                'id'         => $n->id,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
                'data'       => $n->data,
            ];
        });

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'unread' => $unreadCount,
            'items'  => $items,
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user('user');
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    public function read(Request $request, string $id): JsonResponse
    {
        $user = $request->user('user');

        $n = $user->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
        ]);
    }
}

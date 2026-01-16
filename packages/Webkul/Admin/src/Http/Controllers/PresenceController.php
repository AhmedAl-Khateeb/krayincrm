<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Webkul\User\Repositories\UserRepository;
use Carbon\Carbon;

class PresenceController extends Controller
{
    public function __construct(protected UserRepository $userRepository) {}

    public function ping()
    {
        $user = auth()->guard('user')->user();

        if (! $user) {
            return response()->json(['status' => 'guest'], 401);
        }

        $ts = now()->timestamp;

        Cache::put("admin_presence:last_seen:{$user->id}", $ts, 300);

        return response()->json([
            'status'    => 'ok',
            'id'        => $user->id,
            'last_seen' => $ts,
            'ttl'       => 300,
        ]);
    }

    public function agents()
    {
        $ttlSeconds = 300;
        $now = now()->timestamp;

        $admins = $this->userRepository->all(['id', 'name']);

        $items = [];
        $onlineCount = 0;

        foreach ($admins as $admin) {
            $last = Cache::get("admin_presence:last_seen:{$admin->id}");
            $isOnline = $last && (($now - (int) $last) <= $ttlSeconds);

            if ($isOnline) $onlineCount++;

            $lastSeenAt = $last ? Carbon::createFromTimestamp((int) $last) : null;

            $items[] = [
                'id'            => $admin->id,
                'name'          => $admin->name,
                'online'        => $isOnline,
                'last_seen'     => $lastSeenAt ? $lastSeenAt->format('Y-m-d H:i:s') : null,
                'last_seen_ago' => $lastSeenAt ? $lastSeenAt->diffForHumans() : null,
            ];
        }

        return response()->json([
            'online' => $onlineCount,
            'total'  => count($items),
            'items'  => $items,
        ]);
    }
}

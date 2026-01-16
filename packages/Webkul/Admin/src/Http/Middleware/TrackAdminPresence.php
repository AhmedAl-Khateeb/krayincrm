<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class TrackAdminPresence
{
    public function handle($request, Closure $next)
    {
        // Webkul admin guard غالباً user
        $admin = auth()->guard('user')->user();

        if ($admin) {
            $ttlMinutes = 5; // Online لو آخر نشاط خلال 5 دقائق

            Cache::put("admin_presence:last_seen:{$admin->id}", now()->timestamp, now()->addMinutes($ttlMinutes));
            Cache::put("admin_presence:name:{$admin->id}", $admin->name, now()->addMinutes($ttlMinutes));
        }

        return $next($request);
    }
}

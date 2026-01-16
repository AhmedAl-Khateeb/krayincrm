<?php

namespace Webkul\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminPresence
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->guard('user')->user();

        if ($user) {
            Cache::put("admin_presence:last_seen:{$user->id}", now()->timestamp, 300); // 5 min
        }

        return $next($request);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\PresenceController;

Route::get('presence/agents', [PresenceController::class, 'agents'])
    ->name('admin.presence.agents');

Route::post('presence/ping', [PresenceController::class, 'ping'])
    ->name('admin.presence.ping');

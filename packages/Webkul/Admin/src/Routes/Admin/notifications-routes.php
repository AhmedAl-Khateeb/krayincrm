<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\NotificationsController;

Route::get('notifications', [NotificationsController::class, 'index'])
    ->name('admin.notifications.index');

Route::post('notifications/read-all', [NotificationsController::class, 'readAll'])
    ->name('admin.notifications.read_all');

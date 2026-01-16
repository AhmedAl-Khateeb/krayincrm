<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => config('app.admin_url') ?: 'admin',
    'middleware' => ['web', 'auth:user', \Webkul\Admin\Http\Middleware\AdminPresence::class],
], function () {
    require __DIR__ . '/rest-routes.php';
    require __DIR__ . '/presence-routes.php';
    require __DIR__ . '/notifications-routes.php';
    require __DIR__ . '/lead-products-routes.php';
});

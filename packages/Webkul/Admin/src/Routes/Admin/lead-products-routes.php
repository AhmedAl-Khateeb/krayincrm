<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Lead\LeadController;

/*
 * ✅ Get products allowed for a given Lead Type (lead_type_id)
 * Example: /admin/leads/products/by-lead-type?lead_type_id=1
 */
Route::get('leads/products/by-lead-type', [LeadController::class, 'productsByLeadType'])
    ->name('admin.leads.products.by_lead_type');

Route::get('leads/plan-options', [LeadController::class, 'planOptions'])
->name('admin.leads.plan_options');

<?php

namespace Webkul\Installer\Database\Seeders\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run($parameters = [])
    {
        $defaultLocale = $parameters['locale'] ?? config('app.locale');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('roles')->insert([
            [
                'name' => trans('installer::app.seeders.user.role.administrator', [], $defaultLocale),
                'description' => trans('installer::app.seeders.user.role.administrator-role', [], $defaultLocale),
                'permission_type' => 'all',
                'permissions' => null,
            ],

            // =========================
            // Manager
            // =========================
            [
                'name' => 'Manager',
                'description' => 'Manager Role',
                'permission_type' => 'custom',
                'permissions' => json_encode([
                    // MENU
                    'dashboard' => ['view'],
                    'contacts' => ['view'],
                    'leads' => ['view'],

                    // MODULE (UI)
                    'contacts.persons' => ['view', 'create', 'edit', 'delete'],
                    'contacts.organizations' => ['view', 'create', 'edit', 'delete'],
                    'leads' => ['view', 'create', 'edit', 'delete', 'export'],

                    // ROUTES (Middleware)
                    'contacts.persons.view' => ['view'],
                    'contacts.persons.create' => ['view'],
                    'contacts.persons.edit' => ['view'],
                    'contacts.persons.delete' => ['view'],

                    'contacts.organizations.view' => ['view'],
                    'contacts.organizations.create' => ['view'],
                    'contacts.organizations.edit' => ['view'],
                    'contacts.organizations.delete' => ['view'],

                    'leads.view' => ['view'],
                    'leads.create' => ['view'],
                    'leads.edit' => ['view'],
                    'leads.delete' => ['view'],
                    'leads.export' => ['view'],

                    // Settings
                    'settings' => ['view'],
                    'settings.other_settings' => ['view'],
                    'settings.other_settings.web_forms' => ['view'],

                    // Scoped
                    'admin.contacts.persons' => ['view_team', 'create', 'edit_team', 'delete_team'],
                    'admin.contacts.organizations' => ['view_team', 'create', 'edit_team', 'delete_team'],
                    'admin.leads' => ['view_team', 'create', 'edit_team', 'delete_team', 'export'],
                ]),
            ],

            // =========================
            // Sales
            // =========================
            [
                'name' => 'Sales',
                'description' => 'Sales Role',
                'permission_type' => 'custom',
                'permissions' => json_encode([
                    'dashboard' => ['view'],
                    'contacts' => ['view'],
                    'leads' => ['view'],

                    'contacts.persons' => ['view', 'create', 'edit', 'delete', 'export'],
                    'contacts.organizations' => ['view', 'create', 'edit', 'delete', 'export'],
                    'leads' => ['view', 'create', 'edit', 'delete', 'export'],

                    // ROUTES
                    'contacts.persons.view' => ['view'],
                    'contacts.persons.create' => ['view'],
                    'contacts.persons.edit' => ['view'],
                    'contacts.persons.delete' => ['view'],
                    'contacts.persons.export' => ['view'],

                    'contacts.organizations.view' => ['view'],
                    'contacts.organizations.create' => ['view'],
                    'contacts.organizations.edit' => ['view'],
                    'contacts.organizations.delete' => ['view'],
                    'contacts.organizations.export' => ['view'],

                    'leads.view' => ['view'],
                    'leads.create' => ['view'],
                    'leads.edit' => ['view'],
                    'leads.delete' => ['view'],
                    'leads.export' => ['view'],

                    'settings' => ['view'],
                    'settings.other_settings' => ['view'],
                    'settings.other_settings.web_forms' => ['view'],

                    // Scoped own
                    'admin.contacts.persons' => ['view_own', 'create', 'edit_own', 'delete_own'],
                    'admin.contacts.organizations' => ['view_own', 'create', 'edit_own', 'delete_own'],
                    'admin.leads' => ['view_own', 'create', 'edit_own', 'delete_own'],
                ]),
            ],

            // =========================
            // Support
            // =========================
            [
                'name' => 'Support',
                'description' => 'Support Role',
                'permission_type' => 'custom',
                'permissions' => json_encode([
                    'dashboard' => ['view'],
                    'contacts' => ['view'],
                    'leads' => ['view'],

                    'contacts.persons' => ['view', 'edit'],
                    'contacts.organizations' => ['view', 'edit'],
                    'leads' => ['view', 'edit'],

                    // ROUTES
                    'contacts.persons.view' => ['view'],
                    'contacts.persons.edit' => ['view'],

                    'contacts.organizations.view' => ['view'],
                    'contacts.organizations.edit' => ['view'],

                    'leads.view' => ['view'],
                    'leads.edit' => ['view'],

                    'settings' => ['view'],
                    'settings.other_settings' => ['view'],
                    'settings.other_settings.web_forms' => ['view'],

                    // Scoped own
                    'admin.contacts.persons' => ['view_own', 'edit_own'],
                    'admin.contacts.organizations' => ['view_own', 'edit_own'],
                    'admin.leads' => ['view_own', 'edit_own'],
                ]),
            ],

            // =========================
            // Viewer
            // =========================
            [
                'name' => 'Viewer',
                'description' => 'Viewer Role',
                'permission_type' => 'custom',
                'permissions' => json_encode([
                    'dashboard' => ['view'],
                    'contacts' => ['view'],
                    'leads' => ['view'],

                    'contacts.persons' => ['view'],
                    'contacts.organizations' => ['view'],
                    'leads' => ['view'],

                    // ROUTES
                    'contacts.persons.view' => ['view'],
                    'contacts.organizations.view' => ['view'],
                    'leads.view' => ['view'],

                    'settings' => ['view'],
                    'settings.other_settings' => ['view'],
                    'settings.other_settings.web_forms' => ['view'],

                    // Scoped own
                    'admin.contacts.persons' => ['view_own'],
                    'admin.contacts.organizations' => ['view_own'],
                    'admin.leads' => ['view_own'],
                ]),
            ],
        ]);
    }
}

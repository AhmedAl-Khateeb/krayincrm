<?php

namespace Webkul\Installer\Database\Seeders\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run($parameters = [])
    {
        $now = now();

        $users = [
            [
                'id' => 1,
                'name' => 'Example Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('admin123'),
                'status' => 1,
                'role_id' => 1,
                'view_permission' => 'global',
            ],
            [
                'id' => 2,
                'name' => 'Manager One',
                'email' => 'manager1@example.com',
                'password' => bcrypt('manager123'),
                'status' => 1,
                'role_id' => 2,
                'view_permission' => 'group',
            ],
            [
                'id' => 3,
                'name' => 'Sales One',
                'email' => 'sales1@example.com',
                'password' => bcrypt('sales123'),
                'status' => 1,
                'role_id' => 3,
                'view_permission' => 'self',
            ],
            [
                'id' => 4,
                'name' => 'Sales Two',
                'email' => 'sales2@example.com',
                'password' => bcrypt('sales123'),
                'status' => 1,
                'role_id' => 3,
                'view_permission' => 'self',
            ],
            [
                'id' => 5,
                'name' => 'Support One',
                'email' => 'support1@example.com',
                'password' => bcrypt('support123'),
                'status' => 1,
                'role_id' => 4,
                'view_permission' => 'self',
            ],
        ];

        foreach ($users as $u) {
            // ✅ آمن: بيعمل insert لو مش موجود، و update لو موجود
            DB::table('users')->updateOrInsert(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => $u['password'],
                    'status' => $u['status'],
                    'role_id' => $u['role_id'],
                    'view_permission' => $u['view_permission'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}

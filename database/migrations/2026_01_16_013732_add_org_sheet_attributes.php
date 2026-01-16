<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        DB::table('attributes')->insert([
            [
                'code' => 'contact_1',
                'name' => 'Contact 1',
                'type' => 'phone',
                'lookup_type' => null,
                'entity_type' => 'organizations',
                'sort_order' => 10,
                'validation' => null,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 0,
                'is_user_defined' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'contact_2',
                'name' => 'Contact 2',
                'type' => 'phone',
                'lookup_type' => null,
                'entity_type' => 'organizations',
                'sort_order' => 11,
                'validation' => null,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 0,
                'is_user_defined' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'whatsapp_sent',
                'name' => 'WhatsApp Sent',
                'type' => 'boolean',
                'lookup_type' => null,
                'entity_type' => 'organizations',
                'sort_order' => 13,
                'validation' => null,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 0,
                'is_user_defined' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'whatsapp_number',
                'name' => 'WhatsApp Number',
                'type' => 'phone',
                'lookup_type' => null,
                'entity_type' => 'organizations',
                'sort_order' => 14,
                'validation' => null,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 0,
                'is_user_defined' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'remark',
                'name' => 'Remark',
                'type' => 'textarea',
                'lookup_type' => null,
                'entity_type' => 'organizations',
                'sort_order' => 16,
                'validation' => null,
                'is_required' => 0,
                'is_unique' => 0,
                'quick_add' => 0,
                'is_user_defined' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->whereIn('code', [
                'contact_1',
                'contact_2',
                'whatsapp_sent',
                'whatsapp_number',
                'remark',
            ])
            ->delete();
    }
};

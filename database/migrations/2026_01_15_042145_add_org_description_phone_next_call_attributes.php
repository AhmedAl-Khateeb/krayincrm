<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('attributes')->insert([
            [
                'code'           => 'description',
                'name'           => 'Description',
                'type'           => 'textarea',
                'lookup_type'    => null,
                'entity_type'    => 'organizations',
                'sort_order'     => 1,
                'validation'     => null,
                'is_required'    => 0,
                'is_unique'      => 0,
                'quick_add'      => 0,
                'is_user_defined'=> 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'phone',
                'name'           => 'Phone',
                'type'           => 'phone',
                'lookup_type'    => null,
                'entity_type'    => 'organizations',
                'sort_order'     => 2,
                'validation'     => null,
                'is_required'    => 0,
                'is_unique'      => 0,
                'quick_add'      => 0,
                'is_user_defined'=> 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'next_call_at',
                'name'           => 'Next Call Date',
                'type'           => 'date',
                'lookup_type'    => null,
                'entity_type'    => 'organizations',
                'sort_order'     => 3,
                'validation'     => null,
                'is_required'    => 1,  // ✅ تاريخ لازم
                'is_unique'      => 0,
                'quick_add'      => 0,
                'is_user_defined'=> 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->whereIn('code', ['description', 'phone', 'next_call_at'])
            ->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ✅ 1) Insert Attribute
        DB::table('attributes')->insert([
            'code'           => 'call_status',
            'name'           => 'Call Status',
            'type'           => 'select',
            'lookup_type'    => null,
            'entity_type'    => 'organizations',
            'sort_order'     => 50,
            'validation'     => null,
            'is_required'    => 0,
            'is_unique'      => 0,
            'quick_add'      => 0,
            'is_user_defined'=> 1,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $attributeId = DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->where('code', 'call_status')
            ->value('id');

        // ✅ 2) Insert Options
        $options = [
            ['name' => 'Follow UP',         'sort_order' => 1],
            ['name' => 'Waiting For Papers', 'sort_order' => 2],
            ['name' => 'WhatsAPP',           'sort_order' => 3],
            ['name' => 'Call Back',       'sort_order' => 4],
        ];

        
        foreach ($options as $opt) {
            DB::table('attribute_options')->insert([
                'name'         => $opt['name'],
                'sort_order'   => $opt['sort_order'],
                'attribute_id' => $attributeId,
            ]);
        }
    }

    public function down(): void
    {
        $attributeId = DB::table('attributes')
            ->where('entity_type', 'organizations')
            ->where('code', 'call_status')
            ->value('id');

        if ($attributeId) {
            DB::table('attribute_values')->where('attribute_id', $attributeId)->delete();
            DB::table('attribute_options')->where('attribute_id', $attributeId)->delete();
            DB::table('attributes')->where('id', $attributeId)->delete();
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // لو attribute موجود قبل كده ما تكررش
        $exists = DB::table('attributes')
            ->where('code', 'disposition')
            ->where('entity_type', 'leads')
            ->exists();

        if ($exists) {
            return;
        }

        // 1) Insert attribute
        $attributeId = DB::table('attributes')->insertGetId([
            'code'           => 'disposition',
            'name'           => 'Call Disposition',
            'type'           => 'select',
            'lookup_type'    => null,
            'entity_type'    => 'leads',
            'sort_order'     => 999,
            'validation'     => null,
            'is_required'    => 0,
            'is_unique'      => 0,
            'quick_add'      => 1,
            'is_user_defined'=> 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // 2) Detect attribute_options columns (عشان اختلاف النسخ)
        if (!Schema::hasTable('attribute_options')) {
            return;
        }

        $optCols = Schema::getColumnListing('attribute_options');

        $pick = function(array $candidates) use ($optCols) {
            foreach ($candidates as $c) {
                if (in_array($c, $optCols, true)) return $c;
            }
            return null;
        };

        $colAttrId = $pick(['attribute_id']);
        $colName   = $pick(['name','admin_name','label']);
        $colSort   = $pick(['sort_order','position']);

        if (!$colAttrId || !$colName) {
            throw new RuntimeException("attribute_options table missing required columns (attribute_id, name/admin_name/label).");
        }

        $options = [
            'مشغول',
            'مش مهتم',
            'يرد لاحقًا',
            'رقم خطأ',
            'مغلق / Closed',
        ];

        foreach ($options as $i => $label) {
            $row = [
                $colAttrId => $attributeId,
                $colName   => $label,
            ];

            if ($colSort) $row[$colSort] = $i + 1;

            DB::table('attribute_options')->insert($row);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('attributes')) return;

        $attr = DB::table('attributes')
            ->where('code', 'disposition')
            ->where('entity_type', 'leads')
            ->first();

        if (!$attr) return;

        if (Schema::hasTable('attribute_options')) {
            $optCols = Schema::getColumnListing('attribute_options');
            if (in_array('attribute_id', $optCols, true)) {
                DB::table('attribute_options')->where('attribute_id', $attr->id)->delete();
            }
        }

        DB::table('attributes')->where('id', $attr->id)->delete();
    }
};

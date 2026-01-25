<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // لو موجود قبل كده متعملوش تاني
        $exists = DB::table('attributes')
            ->where('entity_type', 'persons')
            ->where('code', 'call_status')
            ->exists();

        if ($exists) return;

        DB::table('attributes')->insert([
            'code'            => 'call_status',
            'name'            => 'Call Status',
            'type'            => 'select',
            'lookup_type'     => null,
            'entity_type'     => 'persons',
            'sort_order'      => 50,
            'validation'      => null,
            'is_required'     => 0,
            'is_unique'       => 0,
            'quick_add'       => 0,
            'is_user_defined' => 1,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $attributeId = DB::table('attributes')
            ->where('entity_type', 'persons')
            ->where('code', 'call_status')
            ->value('id');

        $options = [
            'Bad Debt',
            'Bad experience',
            'Busy',
            'Call Back',
            'DNCL',
            'English Call',
            'Follow UP',
            'Hang up',
            'Hang up 15 Sec',
            'Indian Call',
            'Moved to Team Leader',
            'No Answer',
            'Not intrested',
            'Not Working',
            'Outside Country',
            'Silent Call',
            'Successful Sale',
            'Switched off ( Closed )',
            'Trust issue',
            'Voice Mail',
            'Waiting For Papers',
            'WhatsAPP',
            'Call Dropped',
        ];

        $i = 1;
        foreach ($options as $name) {
            DB::table('attribute_options')->insert([
                'attribute_id' => $attributeId,
                'name'         => $name,
                'sort_order'   => $i++,
            ]);
        }
    }

    public function down(): void
    {
        $attributeId = DB::table('attributes')
            ->where('entity_type', 'persons')
            ->where('code', 'call_status')
            ->value('id');

        if ($attributeId) {
            DB::table('attribute_values')->where('attribute_id', $attributeId)->delete();
            DB::table('attribute_options')->where('attribute_id', $attributeId)->delete();
            DB::table('attributes')->where('id', $attributeId)->delete();
        }
    }
};

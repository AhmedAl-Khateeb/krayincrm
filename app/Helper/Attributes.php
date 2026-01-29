<?php

namespace App\Helper;

use Illuminate\Support\Facades\DB;

class Attributes
{
    protected function addAllLeadAttributesToExportQuery($queryBuilder)
{
    // هات كل Attributes بتاعت leads (ما عدا الأساسيين لو حابب)
    $attributes = DB::table('attributes')
        ->where('entity_type', 'leads')
        ->whereNotIn('code', ['title', 'description']) // اختياري
        ->get(['id', 'code', 'name', 'type']);

    if ($attributes->isEmpty()) {
        return $queryBuilder;
    }

    // join واحد على attribute_values
    $queryBuilder->leftJoin('attribute_values as av', function ($join) {
        $join->on('av.entity_id', '=', 'leads.id')
             ->where('av.entity_type', '=', 'leads');
    });

    // Pivot: كل attribute يتحول لعمود
    foreach ($attributes as $attr) {
        $col = $this->attributeValueColumnByType($attr->type); 
        $alias = 'attr_'.$attr->code;

        $queryBuilder->addSelect(DB::raw(
            "MAX(CASE WHEN av.attribute_id = {$attr->id} THEN av.{$col} END) as {$alias}"
        ));
    }

    return $queryBuilder;
}

protected function attributeValueColumnByType($type): string
{
    // عدّل حسب الأعمدة الفعلية عندك في attribute_values
    return match ($type) {
        'textarea', 'text', 'select', 'multiselect' => 'text_value',
        'integer' => 'integer_value',
        'boolean' => 'boolean_value',
        'date' => 'date_value',
        'datetime' => 'datetime_value',
        'price', 'decimal' => 'decimal_value',
        default => 'text_value',
    };
}

}
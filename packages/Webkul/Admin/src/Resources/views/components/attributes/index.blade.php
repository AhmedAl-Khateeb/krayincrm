@php
    // لو الـ component اتبعتله entity استخدمه، لو لا خليه null
    $entity = $entity ?? ($attributes['entity'] ?? null);
@endphp

@foreach ($customAttributes as $attribute)
    @php
        $validations = [];

        if ($attribute->is_required && request()->routeIs('admin.leads.store')) {
            $validations[] = 'required';
        }

        if ($attribute->type == 'price') {
            $validations[] = 'decimal';
        }

        $validations[] = $attribute->validation;

        $validations = implode('|', array_filter($validations));
    @endphp

    @php
        $attrVal = $entity?->attribute_values?->firstWhere('attribute_id', $attribute->id);

        // 1) قيمة جاية من attribute_values (EAV)
        $eavValue = match ($attribute->type) {
            'date' => $attrVal?->date_value,
            'datetime' => $attrVal?->datetime_value,
            'phone', 'email', 'address' => $attrVal?->json_value,
            default => $attrVal?->text_value,
        };

        // 2) fallback: قيمة جاية من أعمدة جدول leads (column)
        $columnValue = $entity?->{$attribute->code} ?? null;

        // 3) لو الحقل select بيرجع id مخزن كـ integer/ text
        // لو ده expected_close_date (بيحتاج Y-m-d) سواء كان date أو datetime
        if ($attribute->code === 'expected_close_date' && ($eavValue || $columnValue)) {
            $raw = $eavValue ?? $columnValue;
            $columnValue = \Carbon\Carbon::parse($raw)->format('Y-m-d H:i:s');
            $eavValue = null;
        }

        // 4) النهائي: old > eav > column
        $value = old($attribute->code, $eavValue ?? $columnValue);
    @endphp



    <x-admin::form.control-group class="mb-2.5 w-full">
        <x-admin::form.control-group.label for="{{ $attribute->code }}" :class="$attribute->is_required ? 'required' : ''">
            {{ $attribute->name }}

            @if ($attribute->type == 'price')
                <span class="currency-code">({{ core()->currencySymbol(config('app.currency')) }})</span>
            @endif
        </x-admin::form.control-group.label>

        @if (isset($attribute))
            <x-admin::attributes.edit.index :attribute="$attribute" :validations="$validations" :value="$value" />
        @endif

        <x-admin::form.control-group.error :control-name="$attribute->code" />
    </x-admin::form.control-group>
@endforeach

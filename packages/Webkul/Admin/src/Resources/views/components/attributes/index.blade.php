@php
    // لو الـ component اتبعتله entity استخدمه، لو لا خليه null
    $entity = $entity ?? ($attributes['entity'] ?? null);
@endphp

@foreach ($customAttributes as $attribute)
    @php
        $validations = [];

        if ($attribute->is_required) {
            $validations[] = 'required';
        }

        if ($attribute->type == 'price') {
            $validations[] = 'decimal';
        }

        $validations[] = $attribute->validation;

        $validations = implode('|', array_filter($validations));
    @endphp

    @php
        $attrVal = $entity?->attributeValues?->firstWhere('attribute_id', $attribute->id);

        $value = match ($attribute->type) {
            'date' => $attrVal?->date_value,
            'datetime' => $attrVal?->datetime_value,
            'phone', 'email', 'address' => $attrVal?->json_value,
            default => $attrVal?->text_value,
        };
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

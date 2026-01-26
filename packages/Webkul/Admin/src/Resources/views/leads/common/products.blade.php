{!! view_render_event('admin.leads.create.products.form_controls.before') !!}

<v-product-list :data="products"></v-product-list>

{!! view_render_event('admin.leads.create.products.form_controls.after') !!}

@pushOnce('scripts')
    <script type="text/x-template" id="v-product-list-template">
    <div class="flex flex-col gap-4">
        {!! view_render_event('admin.leads.create.products.form_controls.table.before') !!}

        <div class="block w-full overflow-x-auto">
            <x-admin::table>
                {!! view_render_event('admin.leads.create.products.form_controls.table.head.before') !!}

                <x-admin::table.thead>
                    <x-admin::table.thead.tr>
                        <x-admin::table.th>
                            @lang('admin::app.leads.common.products.product-name')
                        </x-admin::table.th>

                        <x-admin::table.th class="text-center">
                            @lang('admin::app.leads.common.products.quantity')
                        </x-admin::table.th>

                        <x-admin::table.th class="text-center">
                            @lang('admin::app.leads.common.products.price')
                        </x-admin::table.th>
                        

                        <x-admin::table.th class="text-center">
                            @lang('admin::app.leads.common.products.plan')
                        </x-admin::table.th>

                        <x-admin::table.th class="text-center">
                            @lang('admin::app.leads.common.products.amount')
                        </x-admin::table.th>

                        <x-admin::table.th class="text-right">
                            @lang('admin::app.leads.common.products.action')
                        </x-admin::table.th>
                    </x-admin::table.thead.tr>
                </x-admin::table.thead>

                {!! view_render_event('admin.leads.create.products.form_controls.table.head.after') !!}

                <x-admin::table.tbody>
                    {!! view_render_event('admin.leads.create.products.form_controls.table.body.product_item.before') !!}

                    <v-product-item
                        v-for="(product, index) in products"
                        :product="product"
                        :key="index"
                        :index="index"
                        :business-type="businessType"
                        :allowed-product-ids="allowedProductIds"
                        :plan-options="planOptions" 
                        @onRemoveProduct="removeProduct($event)"
                    ></v-product-item>

                    {!! view_render_event('admin.leads.create.products.form_controls.table.body.product_item.after') !!}
                </x-admin::table.tbody>
            </x-admin::table>
        </div>

        {!! view_render_event('admin.leads.create.products.form_controls.table.after') !!}

        <button
            type="button"
            class="flex max-w-max items-center gap-2 text-brandColor"
            @click="addProduct"
        >
            <i class="icon-add text-md !text-brandColor"></i>
            @lang('admin::app.leads.common.products.add-more')
        </button>
    </div>
</script>

    <script type="text/x-template" id="v-product-item-template">
    <x-admin::table.tbody.tr>
        <x-admin::table.td>
            <x-admin::form.control-group class="!mb-0">
                <x-admin::lookup
                    ::src="src"
                    ::name="`${inputName}[name]`"
                    ::params="lookupParams"
                    :placeholder="trans('admin::app.leads.common.products.product-name')"
                    @on-selected="(p) => addProduct(p)"
                    ::value="{ id: product.product_id, name: product.name }"
                />

                <x-admin::form.control-group.control
                    type="hidden"
                    ::name="`${inputName}[product_id]`"
                    v-model="product.product_id"
                    rules="required"
                    :label="trans('admin::app.leads.common.products.product-name')"
                />

                <x-admin::form.control-group.error ::name="`${inputName}[product_id]`" />
            </x-admin::form.control-group>
        </x-admin::table.td>

        <x-admin::table.td class="text-right">
            <x-admin::form.control-group>
                <x-admin::form.control-group.control
                    type="inline"
                    ::name="`${inputName}[quantity]`"
                    ::value="product.quantity"
                    rules="required|decimal:4"
                    :label="trans('admin::app.leads.common.products.quantity')"
                    :placeholder="trans('admin::app.leads.common.products.quantity')"
                    @on-change="(event) => product.quantity = event.value"
                    position="center"
                />
            </x-admin::form.control-group>
        </x-admin::table.td>

        <x-admin::table.td class="text-right">
            <x-admin::form.control-group>
                <x-admin::form.control-group.control
                    type="inline"
                    ::name="`${inputName}[price]`"
                    ::value="product.price"
                    rules="required|decimal:4"
                    :label="trans('admin::app.leads.common.products.price')"
                    :placeholder="trans('admin::app.leads.common.products.price')"
                    @on-change="(event) => product.price = event.value"
                    ::value-label="$admin.formatPrice(product.price)"
                    position="center"
                />
            </x-admin::form.control-group>
        </x-admin::table.td>

        <x-admin::table.td class="text-center">
    <x-admin::form.control-group class="!mb-0">
        <select
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm
                   dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            :name="`${inputName}[plan_option_id]`"
            v-model="product.plan_option_id"
        >
            <option value="" disabled>{{ trans('admin::app.leads.common.products.select-plan') }}</option>

            <option
                v-for="opt in planOptions"
                :key="opt.id"
                :value="opt.id"
            >
                @{{ opt.label }}
            </option>
        </select>
    </x-admin::form.control-group>
</x-admin::table.td>


        <x-admin::table.td class="text-right">
            <x-admin::form.control-group>
                <x-admin::form.control-group.control
                    type="inline"
                    ::name="`${inputName}[amount]`"
                    ::value="product.price * product.quantity"
                    rules="required|decimal:4"
                    :label="trans('admin::app.leads.common.products.total')"
                    ::value-label="$admin.formatPrice(product.price * product.quantity)"
                    :allowEdit="false"
                    position="center"
                />
            </x-admin::form.control-group>
        </x-admin::table.td>

        <x-admin::table.td class="text-right">
            <x-admin::form.control-group>
                <i @click="removeProduct" class="icon-delete cursor-pointer text-2xl"></i>
            </x-admin::form.control-group>
        </x-admin::table.td>
    </x-admin::table.tbody.tr>
</script>

    <script type="module">
        app.component('v-product-list', {
            template: '#v-product-list-template',
            props: ['data'],

            data() {
                return {
                    products: Array.isArray(this.data) ? this.data : [],
                    allowedProductIds: [],
                    planOptions: [],
                };
            },

            mounted() {
                window.addEventListener('lead-products-options-updated', this.onProductsOptionsUpdated);

                window.addEventListener('lead-plan-options-updated', this.onPlanOptionsUpdated);

                // ✅ fallback لو الـ event متأخر/ضاع
                this.fetchPlanOptions();

                if (!this.products.length) this.addProduct();
            },


            beforeUnmount() {
                window.removeEventListener('lead-products-options-updated', this.onProductsOptionsUpdated);
                window.removeEventListener('lead-plan-options-updated', this.onPlanOptionsUpdated);
            },

            methods: {
                onProductsOptionsUpdated(e) {
                    const payload = e?.detail || {};
                    const items = Array.isArray(payload.items) ? payload.items : [];

                    this.allowedProductIds = items
                        .map(x => x?.id)
                        .filter(x => x !== null && x !== undefined);

                    if (payload.clearSelected) {
                        this.products = this.products.map(p => ({
                            ...p,
                            product_id: null,
                            name: '',
                            price: 0,
                            quantity: 1,
                            amount: null,
                            plan_option_id: null,
                        }));
                    }

                    if (!this.products.length) {
                        this.addProduct();
                    }
                },


                addProduct() {
                    this.products.push({
                        id: null,
                        product_id: null,
                        name: '',
                        quantity: 1,
                        price: 0,
                        amount: null,
                        plan_option_id: null,
                    });
                },

                removeProduct(product) {
                    const index = this.products.indexOf(product);
                    if (index >= 0) this.products.splice(index, 1);
                },

                async fetchPlanOptions() {
                    try {
                        const url = `{{ route('admin.leads.plan_options') }}`;
                        const res = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();

                        this.planOptions = data.items || [];
                    } catch (e) {
                        console.error('fetchPlanOptions failed', e);
                    }
                },

                onPlanOptionsUpdated(e) {
                    this.planOptions = (e?.detail?.items || []);
                },

            },
        });

        app.component('v-product-item', {
            template: '#v-product-item-template',

            props: ['index', 'product', 'allowedProductIds', 'planOptions', 'businessType'],

            computed: {
                inputName() {
                    return this.product.id ?
                        `products[${this.product.id}]` :
                        `products[product_${this.index}]`;
                },

                src() {
                    return "{{ route('admin.products.search') }}";
                },

                lookupParams() {
                    const ids = (this.allowedProductIds || []).filter(x => x != null);

                    // لو مفيش ids، ماتبعتهوش عشان مايفلترش غلط
                    const base = {
                        query: this.product.name,
                        business_type: this.businessType,
                    };

                    if (ids.length) {
                        base.allowed_ids = ids.join(',');
                    }

                    return base;
                },
            },

            methods: {
                addProduct(result) {
                    this.product.product_id = result.id;
                    this.product.name = result.name;
                    this.product.price = result.price;
                    this.product.quantity = result.quantity ?? 1;
                },

                removeProduct() {
                    this.$emit('onRemoveProduct', this.product);
                },
            },
        });
    </script>
@endPushOnce

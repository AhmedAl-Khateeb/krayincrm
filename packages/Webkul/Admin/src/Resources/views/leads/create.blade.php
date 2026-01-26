<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.leads.create.title')
    </x-slot>

    {!! view_render_event('admin.leads.create.form.before') !!}

    <x-admin::form :action="route('admin.leads.store')">
        <div class="flex flex-col gap-4">
            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="leads.create" />

                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.leads.create.title')
                    </div>
                </div>

                {!! view_render_event('admin.leads.create.save_button.before') !!}

                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.leads.create.form_buttons.before') !!}

                    <button type="submit" class="primary-button">
                        @lang('admin::app.leads.create.save-btn')
                    </button>

                    {!! view_render_event('admin.leads.create.form_buttons.after') !!}
                </div>

                {!! view_render_event('admin.leads.create.save_button.after') !!}
            </div>

            @if (request('stage_id'))
                <input type="hidden" id="lead_pipeline_stage_id" name="lead_pipeline_stage_id"
                    value="{{ request('stage_id') }}" />
            @endif

            @if (request('pipeline_id'))
                <input type="hidden" id="lead_pipeline_id" name="lead_pipeline_id"
                    value="{{ request('pipeline_id') }}" />
            @endif

            <v-lead-create>
                <x-admin::shimmer.leads.datagrid />
            </v-lead-create>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.leads.create.form.after') !!}

    @pushOnce('scripts')
        <script type="text/x-template" id="v-lead-create-template">
            <div class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                {!! view_render_event('admin.leads.edit.form_controls.before') !!}

                <div class="flex w-full gap-2 border-b border-gray-200 dark:border-gray-800">
                    <template v-for="tab in tabs" :key="tab.id">
                        {!! view_render_event('admin.leads.create.tabs.before') !!}

                        <a
                            :href="'#' + tab.id"
                            :class="[
                                'inline-block px-3 py-2.5 border-b-2 text-sm font-medium',
                                activeTab === tab.id
                                    ? 'text-brandColor border-brandColor'
                                    : 'text-gray-600 dark:text-gray-300 border-transparent hover:text-gray-800 hover:border-gray-400 dark:hover:border-gray-400 dark:hover:text-white'
                            ]"
                            @click="scrollToSection(tab.id)"
                        >
                            @{{ tab.label }}
                        </a>

                        {!! view_render_event('admin.leads.create.tabs.after') !!}
                    </template>
                </div>

                <div class="flex flex-col gap-4 px-4 py-2">
                    {!! view_render_event('admin.leads.create.details.before') !!}

                    <div class="flex flex-col gap-4" id="lead-details">
                        <div class="flex flex-col gap-1">
                            <p class="text-base font-semibold dark:text-white">
                                @lang('admin::app.leads.create.details')
                            </p>

                            <p class="text-gray-600 dark:text-white">
                                @lang('admin::app.leads.create.details-info')
                            </p>
                        </div>

                        <div class="w-1/2 max-md:w-full">
                            {!! view_render_event('admin.leads.create.details.attributes.before') !!}

                            {{-- ✅ Attributes include lead_type_id select already --}}
                            <x-admin::attributes
                                :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                                    ['code', 'NOTIN', ['lead_value', 'lead_type_id', 'lead_source_id', 'expected_close_date', 'user_id', 'lead_pipeline_id', 'lead_pipeline_stage_id', 'disposition']],
                                    'entity_type' => 'leads',
                                    'quick_add'   => 1
                                ])"
                                :custom-validations="[
                                    'expected_close_date' => [
                                        'date_format:yyyy-MM-dd',
                                        'after:' .  \Carbon\Carbon::yesterday()->format('Y-m-d')
                                    ],
                                ]"
                            />

                            <div class="flex gap-4 max-sm:flex-wrap">
                                <div class="w-full">
                                    <x-admin::attributes
                                        :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                                            ['code', 'IN', ['lead_value', 'lead_type_id', 'lead_source_id']],
                                            'entity_type' => 'leads',
                                            'quick_add'   => 1
                                        ])"
                                        :custom-validations="[
                                            'expected_close_date' => [
                                                'date_format:yyyy-MM-dd',
                                                'after:' .  \Carbon\Carbon::yesterday()->format('Y-m-d')
                                            ],
                                        ]"
                                    />
                                </div>

                                <div class="w-full">
                                    <x-admin::attributes
                                        :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                                            ['code', 'IN', ['expected_close_date', 'user_id', 'disposition']],
                                            'entity_type' => 'leads',
                                            'quick_add'   => 1
                                        ])"
                                        :custom-validations="[
                                            'expected_close_date' => [
                                                'date_format:yyyy-MM-dd',
                                                'after:' .  \Carbon\Carbon::yesterday()->format('Y-m-d')
                                            ],
                                        ]"
                                    />
                                </div>
                            </div>

                            {!! view_render_event('admin.leads.create.details.attributes.after') !!}
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.create.details.after') !!}

                    {!! view_render_event('admin.leads.create.contact_person.before') !!}

                    <div class="flex flex-col gap-4" id="contact-person">
                        <div class="flex flex-col gap-1">
                            <p class="text-base font-semibold dark:text-white">
                                @lang('admin::app.leads.create.contact-person')
                            </p>

                            <p class="text-gray-600 dark:text-white">
                                @lang('admin::app.leads.create.contact-info')
                            </p>
                        </div>

                        <div class="w-1/2 max-md:w-full">
                            @include('admin::leads.common.contact')
                        </div>
                    </div>

                    {!! view_render_event('admin.leads.create.contact_person.after') !!}

                    <div class="flex flex-col gap-4" id="products">
                        <div class="flex flex-col gap-1">
                            <p class="text-base font-semibold dark:text-white">
                                @lang('admin::app.leads.create.products')
                            </p>

                            <p class="text-gray-600 dark:text-white">
                                @lang('admin::app.leads.create.products-info')
                            </p>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Lead Type ID: <strong>@{{ leadTypeId || '-' }}</strong>
                                <span v-if="productsLoading"> - Loading products...</span>
                                <span v-else> - Loaded: @{{ productsOptions.length }}</span>
                            </p>
                        </div>

                        <div>
                            @include('admin::leads.common.products')
                        </div>
                    </div>
                </div>

                {!! view_render_event('admin.leads.form_controls.after') !!}
            </div>
        </script>

        <script type="module">
            app.component('v-lead-create', {
                template: '#v-lead-create-template',

                data() {
                    return {
                        activeTab: 'lead-details',
                        tabs: [{
                                id: 'lead-details',
                                label: @json(trans('admin::app.leads.create.details'))
                            },
                            {
                                id: 'contact-person',
                                label: @json(trans('admin::app.leads.create.contact-person'))
                            },
                            {
                                id: 'products',
                                label: @json(trans('admin::app.leads.create.products'))
                            },
                        ],

                        leadTypeId: null,
                        productsOptions: [],
                        productsLoading: false,
                    };
                },

                mounted() {
                    this.initLeadTypeWatcher();
                    this.loadPlanOptions();
                },

                methods: {
                    scrollToSection(tabId) {
                        const section = document.getElementById(tabId);
                        if (section) section.scrollIntoView({
                            behavior: 'smooth'
                        });
                    },

                    initLeadTypeWatcher() {
                        const select = document.querySelector('[name="lead_type_id"]');

                        if (!select) {
                            console.warn('lead_type_id select not found');
                            return;
                        }

                        this.leadTypeId = select.value ? parseInt(select.value) : null;

                        this.loadProductsByLeadType(false);

                        select.addEventListener('change', () => {
                            this.leadTypeId = select.value ? parseInt(select.value) : null;
                            this.loadProductsByLeadType(true);
                        });
                    },

                    async loadProductsByLeadType(clearSelected = false) {
                        this.productsLoading = true;

                        try {
                            const id = this.leadTypeId || 0;
                            const url = `{{ route('admin.leads.products.by_lead_type') }}?lead_type_id=${id}`;

                            const res = await fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await res.json();

                            this.productsOptions = data.items || [];

                            window.dispatchEvent(new CustomEvent('lead-products-options-updated', {
                                detail: {
                                    lead_type_id: id,
                                    items: this.productsOptions,
                                    clearSelected: clearSelected,
                                }
                            }));
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.productsLoading = false;
                        }
                    },

                    async loadPlanOptions() {
                        try {
                            const url = `{{ route('admin.leads.plan_options') }}`;

                            const res = await fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await res.json();

                            window.dispatchEvent(new CustomEvent('lead-plan-options-updated', {
                                detail: {
                                    items: data.items || []
                                }
                            }));
                        } catch (e) {
                            console.error('loadPlanOptions failed', e);
                        }
                    },

                },
            });
        </script>
    @endPushOnce

    @pushOnce('styles')
        <style>
            html {
                scroll-behavior: smooth;
            }
        </style>
    @endPushOnce
</x-admin::layouts>

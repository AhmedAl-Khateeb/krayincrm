<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.contacts.organizations.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                {!! view_render_event('admin.organizations.index.breadcrumbs.before') !!}

                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="contacts.organizations" />

                {!! view_render_event('admin.organizations.index.breadcrumbs.before') !!}
                
                <div class="text-xl font-bold dark:text-gray-300">
                    @lang('admin::app.contacts.organizations.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <div class="flex items-center gap-x-2.5">
                    {!! view_render_event('admin.organizations.index.create_button.before') !!}

                    @if (bouncer()->hasPermission('contacts.organizations'))
                        <!-- Create button for person -->
                        <a
                            href="{{ route('admin.contacts.organizations.create') }}"
                            class="primary-button"
                        >
                            @lang('admin::app.contacts.organizations.index.create-btn')
                        </a>
                    @endif

                    {!! view_render_event('admin.organizations.index.create_button.after') !!}
                </div>
            </div>
        </div>

        {!! view_render_event('admin.organizations.datagrid.index.before') !!}

      {{-- <x-admin::datagrid :src="route('admin.contacts.organizations.index')" :isMultiRow="true">
    <x-admin::shimmer.datagrid :is-multi-row="true"/>
</x-admin::datagrid> --}}


        {!! view_render_event('admin.organizations.datagrid.index.after') !!}
    </div>

<v-followup-persons>
    <x-admin::shimmer.datagrid :is-multi-row="true"/>
</v-followup-persons>

@pushOnce('scripts')
<script type="text/x-template" id="v-followup-persons-template">
    <x-admin::datagrid
        src="{{ route('admin.contacts.organizations.index') }}"
        :isMultiRow="true"
        ref="datagrid"
    >
        {{-- ✅ هنا هننسخ نفس slots بتاع persons --}}

        {{-- HEADER --}}
        <template #header="{ isLoading, available, applied, selectAll, sort }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                {{-- نفس header بتاع persons --}}
                <div class="row grid grid-cols-[.1fr_.2fr_.2fr_.2fr_.2fr_.2fr] items-center border-b px-4 py-2.5 dark:border-gray-800 max-lg:hidden">

                    {{-- checkbox + ID --}}
                    <div class="flex items-center gap-2.5">
                        <label class="flex w-max cursor-pointer items-center gap-1" for="mass_action_select_all_records">
                            <input
                                type="checkbox"
                                id="mass_action_select_all_records"
                                class="peer hidden"
                                :checked="['all','partial'].includes(applied.massActions.meta.mode)"
                                @change="selectAll"
                            />
                            <span
                                class="icon-checkbox-outline rounded-md text-2xl text-gray-600 dark:text-gray-300"
                                :class="[
                                    applied.massActions.meta.mode === 'all'
                                        ? 'peer-checked:icon-checkbox-select peer-checked:text-brandColor'
                                        : (applied.massActions.meta.mode === 'partial'
                                            ? 'peer-checked:icon-checkbox-multiple peer-checked:text-brandColor'
                                            : '')
                                ]"
                            ></span>
                        </label>

                        <p class="text-gray-600 dark:text-gray-300 cursor-pointer"
                           @click="sort(available.columns.find(c => c.index === 'id'))">
                            @{{ available.columns.find(c => c.index === 'id')?.label }}
                        </p>
                    </div>

                    {{-- باقي الأعمدة --}}
                    <p class="text-gray-600 dark:text-gray-300 cursor-pointer"
                       @click="sort(available.columns.find(c => c.index === 'person_name'))">
                        @{{ available.columns.find(c => c.index === 'person_name')?.label }}
                    </p>

                    <p class="text-gray-600 dark:text-gray-300">
                        @{{ available.columns.find(c => c.index === 'emails')?.label }}
                    </p>

                    <p class="text-gray-600 dark:text-gray-300">
                        @{{ available.columns.find(c => c.index === 'contact_numbers')?.label }}
                    </p>

                    <p class="text-gray-600 dark:text-gray-300">
                        @{{ available.columns.find(c => c.index === 'organization')?.label }}
                    </p>

                    <p class="text-gray-600 dark:text-gray-300 text-right">
                        Actions
                    </p>
                </div>
            </template>
        </template>

        {{-- BODY --}}
        <template #body="{ isLoading, available, applied, performAction }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <div
                    class="row grid grid-cols-[.1fr_.2fr_.2fr_.2fr_.2fr_.2fr] border-b px-4 py-2.5 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950 max-lg:hidden"
                    v-for="record in available.records"
                >
                    {{-- checkbox + ID --}}
                    <div class="flex items-center gap-2.5">
                        <input
                            type="checkbox"
                            class="peer hidden"
                            :id="`mass_action_select_record_${record.id}`"
                            :value="record.id"
                            v-model="applied.massActions.indices"
                        />
                        <label
                            class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor dark:text-gray-300"
                            :for="`mass_action_select_record_${record.id}`"
                        ></label>

                        <span class="dark:text-gray-300">@{{ record.id }}</span>
                    </div>

                    {{-- ✅ Avatar + name (نفس persons) --}}
                    <div class="flex items-center gap-2 dark:text-gray-300">
                        <x-admin::avatar ::name="record.person_name" />
                        <span>@{{ record.person_name }}</span>
                    </div>

                    <p class="dark:text-gray-300">@{{ record.emails }}</p>

                    {{-- هنا هيظهر رقم + علامة الاتصال اللي عملتها --}}
                    <p class="dark:text-gray-300" v-html="record.contact_numbers"></p>

                    <p class="dark:text-gray-300">@{{ record.organization }}</p>

                    {{-- actions --}}
                    <div class="flex items-center justify-end gap-x-3">
                        <span
                            v-for="action in record.actions"
                            class="cursor-pointer rounded-md p-1.5 text-2xl hover:bg-gray-200 dark:hover:bg-gray-800"
                            :class="action.icon"
                            @click="performAction(action)"
                        ></span>
                    </div>
                </div>
            </template>
        </template>

    </x-admin::datagrid>
</script>

<script type="module">
    app.component('v-followup-persons', { template: '#v-followup-persons-template' });
</script>
@endPushOnce





</x-admin::layouts>


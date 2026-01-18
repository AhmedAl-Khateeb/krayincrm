<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.contacts.persons.create.title')
    </x-slot>

    {!! view_render_event('admin.persons.create.form.before') !!}

    <x-admin::form :action="route('admin.contacts.persons.store')" enctype="multipart/form-data">
        <div class="flex flex-col gap-4">

            <div
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="contacts.persons.create" />
                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.contacts.persons.create.title')
                    </div>
                </div>

                <button type="submit" class="primary-button">
                    @lang('admin::app.contacts.persons.create.save-btn')
                </button>
            </div>

            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                {!! view_render_event('admin.persons.create.form_controls.before') !!}

                @php
                    $me = auth()->guard('admin')->user();
                    $isAdmin = optional($me->role)->permission_type === 'all';

                    $users = $isAdmin
                        ? \Webkul\User\Models\User::query()
                            ->orderBy('name')
                            ->get(['id', 'name'])
                        : collect();
                @endphp

                {{-- ✅ Sales Owner --}}
                @if ($isAdmin)
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Sales Owner</label>
                        <select name="user_id"
                            class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-gray-900 dark:text-white"
                            required>
                            <option value="">-- Choose User --</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Sales Owner</label>
                        <input type="text" value="{{ $me->name }}"
                            class="w-full rounded-lg border px-3 py-2 bg-gray-50 dark:bg-gray-800 dark:text-white"
                            disabled />
                    </div>

                    <input type="hidden" name="user_id" value="{{ $me->id }}">
                @endif

                {{-- ✅ مهم: استبعد user_id عشان الـ Lookup ما يظهرش --}}
                <x-admin::attributes :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                    ['code', 'NOTIN', ['organization_id', 'user_id']],
                    'entity_type' => 'persons',
                ])" :custom-validations="[
                    'name' => ['min:2', 'max:100'],
                    'job_title' => ['max:100'],
                ]" />

                <v-organization></v-organization>

                {!! view_render_event('admin.persons.create.form_controls.after') !!}
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('admin.persons.create.form.after') !!}

    @pushOnce('scripts')
        <script type="text/x-template" id="v-organization-template">
            <div>
                <x-admin::attributes
                    :custom-attributes="app('Webkul\Attribute\Repositories\AttributeRepository')->findWhere([
                        ['code', 'IN', ['organization_id']],
                        'entity_type' => 'persons',
                    ])"
                />

                <template v-if="organizationName">
                    <x-admin::form.control-group.control
                        type="hidden"
                        name="organization_name"
                        v-model="organizationName"
                    />
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-organization', {
                template: '#v-organization-template',

                data() {
                    return {
                        organizationName: null
                    };
                },

                methods: {
                    // لو عندك Event بيتبعت من lookup هنوصلّه هنا بعدين
                    handleLookupAdded(event) {
                        this.organizationName = event?.name || null;
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

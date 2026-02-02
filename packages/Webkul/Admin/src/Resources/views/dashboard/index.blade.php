<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.dashboard.index.title')
    </x-slot>

    <!-- Head Details Section -->
    {!! view_render_event('admin.dashboard.index.header.before') !!}



    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        {!! view_render_event('admin.dashboard.index.header.left.before') !!}

        <div class="grid gap-1.5">
            <p class="text-2xl font-semibold dark:text-white">
                @lang('admin::app.dashboard.index.title')
            </p>
        </div>

        {!! view_render_event('admin.dashboard.index.header.left.after') !!}

        <!-- Actions -->
        {!! view_render_event('admin.dashboard.index.header.right.before') !!}

        <!-- 🔔 Notifications Card -->
        {{-- <div class="mb-4 w-full rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div class="flex flex-col">
                    <strong class="text-sm text-gray-800 dark:text-white">Notifications</strong>
                    <span id="notif-card-sub" class="text-[11px] text-gray-500 dark:text-gray-400">Loading...</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="notif-card-refresh"
                        class="rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-700 hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                        Refresh
                    </button>

                    <button type="button" id="notif-card-readall" class="text-xs text-brandColor hover:underline">
                        Mark all as read
                    </button>
                </div>
            </div>

            <!-- LIST -->
            <div id="notif-card-list" class="mt-3 space-y-2 max-h-[250px] overflow-y-auto">
            </div>
        </div> --}}

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                (function() {
                    const listEl = document.getElementById('notif-card-list');
                    const subEl = document.getElementById('notif-card-sub');
                    const btnRefresh = document.getElementById('notif-card-refresh');
                    const btnReadAll = document.getElementById('notif-card-readall');
                    const bellCount = document.getElementById('notif-bell-count');

                    const URL_INDEX = "{{ route('admin.notifications.index') }}";
                    const URL_READALL = "{{ route('admin.notifications.read_all') }}";
                    const URL_READONE = "{{ url('admin/notifications/read') }}";
                    const CSRF = "{{ csrf_token() }}";

                    function esc(str) {
                        return String(str ?? '')
                            .replaceAll('&', '&amp;')
                            .replaceAll('<', '&lt;')
                            .replaceAll('>', '&gt;')
                            .replaceAll('"', '&quot;')
                            .replaceAll("'", "&#039;");
                    }

                    function render(items) {
                        if (!Array.isArray(items) || items.length === 0) {
                            listEl.innerHTML =
                                `<div class="text-xs text-gray-500 dark:text-gray-400">No notifications.</div>`;
                            return;
                        }

                        listEl.innerHTML = items.map(n => `
                <div class="notif-item cursor-pointer rounded-md border border-gray-100 bg-gray-50 p-2 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-950"
                    data-id="${n.id}" data-url="${esc(n.url || '')}">
                    <div class="text-sm font-semibold text-gray-800 dark:text-white">${esc(n.title)}</div>
                    ${n.body ? `<div class="mt-0.5 text-xs text-gray-700 dark:text-gray-200">${esc(n.body)}</div>` : ''}
                    <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">${esc(n.created_at)}</div>
                </div>
            `).join('');
                    }

                    async function loadNotifications() {
                        try {
                            const res = await fetch(URL_INDEX + '?limit=10', {
                                headers: {
                                    "Accept": "application/json",
                                    "X-Requested-With": "XMLHttpRequest"
                                }
                            });
                            const data = await res.json();
                            const unread = parseInt(data.unread_count || 0);

                            subEl.textContent = unread ? `${unread} unread — auto refresh every 5s` :
                                `All caught up — auto refresh every 5s`;

                            if (bellCount) {
                                bellCount.textContent = unread;
                                bellCount.style.display = unread ? 'inline-flex' : 'none';
                            }

                            render(data.items || []);
                        } catch (e) {
                            subEl.textContent = "Failed to load notifications";
                            console.error(e);
                        }
                    }

                    async function markAllRead() {
                        try {
                            await fetch(URL_READALL, {
                                method: "POST",
                                headers: {
                                    "Accept": "application/json",
                                    "X-Requested-With": "XMLHttpRequest",
                                    "X-CSRF-TOKEN": CSRF
                                }
                            });
                            loadNotifications();
                        } catch (e) {
                            console.error(e);
                        }
                    }

                    listEl.addEventListener('click', async (e) => {
                        const item = e.target.closest('.notif-item');
                        if (!item) return;

                        const id = item.dataset.id;
                        const url = item.dataset.url || '#';

                        try {
                            await fetch(`${URL_READONE}/${id}`, {
                                method: "POST",
                                headers: {
                                    "Accept": "application/json",
                                    "X-Requested-With": "XMLHttpRequest",
                                    "X-CSRF-TOKEN": CSRF
                                }
                            });
                        } catch (err) {
                            console.error(err);
                        }

                        loadNotifications(); // تحديث القائمة بعد القراءة

                        if (url !== '#') window.location.href = url; // توجه للصفحة فقط إذا URL صالح
                    });


                    btnRefresh.addEventListener('click', loadNotifications);
                    btnReadAll.addEventListener('click', markAllRead);

                    loadNotifications();
                    setInterval(loadNotifications, 5000);

                })();
            });
        </script>





        <script>
            async function handleNotifClick(el) {
                const id = el.dataset.id;
                const url = el.dataset.url;

                try {
                    await fetch("{{ url('admin/notifications/read') }}/" + id, {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        }
                    });
                } catch (e) {
                    console.error(e);
                }

                // روح للصفحة
                if (url) {
                    window.location.href = url;
                }
            }
        </script>



        {!! view_render_event('admin.dashboard.index.header.right.after') !!}

    </div>

    {!! view_render_event('admin.dashboard.index.header.after') !!}

    <!-- Body Component -->
    {!! view_render_event('admin.dashboard.index.content.before') !!}

    <div class="mt-3.5 flex gap-4 max-xl:flex-wrap">
        <!-- Left Section -->
        {!! view_render_event('admin.dashboard.index.content.left.before') !!}

        <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
            <!-- Revenue Stats -->
            @include('admin::dashboard.index.revenue')

            <!-- Over All Stats -->
            @include('admin::dashboard.index.over-all')

            <!-- Total Leads Stats -->
            @include('admin::dashboard.index.total-leads')

            <div class="flex gap-4 max-lg:flex-wrap">
                <!-- Total Products -->
                @include('admin::dashboard.index.top-selling-products')

                <!-- Total Persons -->
                @include('admin::dashboard.index.top-persons')
            </div>
        </div>

        {!! view_render_event('admin.dashboard.index.content.left.after') !!}

        <!-- Right Section -->
        {!! view_render_event('admin.dashboard.index.content.right.before') !!}

        <div class="flex w-[378px] max-w-full flex-col gap-4 max-sm:w-full">
            <!-- Revenue by Types -->
            @include('admin::dashboard.index.open-leads-by-states')

            <!-- Revenue by Sources -->
            @include('admin::dashboard.index.revenue-by-sources')

            <!-- Revenue by Types -->
            @include('admin::dashboard.index.revenue-by-types')

            <!-- Agents Box -->
            <div
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <strong class="text-sm dark:text-white">Agents</strong>
                    <span id="agents-summary" class="text-[11px] text-gray-500 dark:text-gray-400"></span>
                </div>

                <div id="agents-box" class="mt-2 space-y-2"></div>
            </div>
        </div>

        {!! view_render_event('admin.dashboard.index.content.right.after') !!}
    </div>

    {!! view_render_event('admin.dashboard.index.content.after') !!}

    <!-- Toast Container -->
    <div id="notif-toast-wrap" class="fixed right-4 top-4 z-[9999] space-y-2"></div>

    @pushOnce('scripts')
        <script type="module" src="{{ vite()->asset('js/chart.js') }}"></script>
        <script type="module" src="https://cdn.jsdelivr.net/npm/chartjs-chart-funnel@4.2.1/build/index.umd.min.js"></script>

        {{-- =======================
            Presence (Agents)
        ======================== --}}
        <script>
            async function pingPresence() {
                try {
                    await fetch("{{ route('admin.presence.ping') }}", {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        }
                    });
                } catch (e) {}
            }

            async function loadAgents() {
                try {
                    const res = await fetch("{{ route('admin.presence.agents') }}", {
                        headers: {
                            "Accept": "application/json"
                        }
                    });

                    const data = await res.json();

                    const summary = document.getElementById('agents-summary');
                    if (summary) summary.textContent = `${data.online || 0}/${data.total || 0} online`;

                    const box = document.getElementById('agents-box');
                    if (!box) return;

                    box.innerHTML = (data.items || []).map(u => `
                        <div class="flex items-start gap-2">
                            <span style="
                                width:8px;height:8px;margin-top:4px;border-radius:50%;
                                background:${u.online ? '#16a34a' : '#9ca3af'};
                                flex-shrink:0;
                            "></span>

                            <div style="display:flex;flex-direction:column;line-height:1.2">
                                <span class="font-semibold dark:text-white">${u.name}</span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                    ${u.online ? 'Online الآن' : (u.last_seen_ago ? `آخر ظهور ${u.last_seen_ago}` : 'لم يتصل بعد')}
                                </span>
                            </div>
                        </div>
                    `).join('');
                } catch (e) {}
            }

            pingPresence();
            loadAgents();

            setInterval(() => {
                pingPresence();
                loadAgents();
            }, 15000);
        </script>


        {{-- =======================
            Dashboard Filters (Vue)
        ======================== --}}
        <script type="text/x-template" id="v-dashboard-filters-template">
            {!! view_render_event('admin.dashboard.index.date_filters.before') !!}

            <div class="flex gap-1.5">
                <x-admin::flat-picker.date
                    class="!w-[140px]"
                    ::allow-input="false"
                    ::max-date="filters.end"
                >
                    <input
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        v-model="filters.start"
                        placeholder="@lang('admin::app.dashboard.index.start-date')"
                    />
                </x-admin::flat-picker.date>

                <x-admin::flat-picker.date
                    class="!w-[140px]"
                    ::allow-input="false"
                    ::max-date="filters.end"
                >
                    <input
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        v-model="filters.end"
                        placeholder="@lang('admin::app.dashboard.index.end-date')"
                    />
                </x-admin::flat-picker.date>
            </div>

            {!! view_render_event('admin.dashboard.index.date_filters.after') !!}
        </script>

        <script type="module">
            app.component('v-dashboard-filters', {
                template: '#v-dashboard-filters-template',

                data() {
                    return {
                        filters: {
                            channel: '',
                            start: "{{ $startDate->format('Y-m-d') }}",
                            end: "{{ $endDate->format('Y-m-d') }}",
                        }
                    }
                },

                watch: {
                    filters: {
                        handler() {
                            this.$emitter.emit('reporting-filter-updated', this.filters);
                        },
                        deep: true
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

<header
    class="sticky top-0 z-[10001] flex items-center justify-between gap-1 border-b border-gray-200 bg-white px-4 py-2.5 transition-all dark:border-gray-800 dark:bg-gray-900">
    <!-- logo -->
    <div class="flex items-center gap-1.5">
        <!-- Sidebar Menu -->
        <x-admin::layouts.sidebar.mobile />
        <a href="{{ route('admin.dashboard.index') }}">
            @if ($logo = core()->getConfigData('general.general.admin_logo.logo_image'))
                <img class="h-10" src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" />
            @else
                <img class="h-10 max-sm:hidden"
                    src="{{ request()->cookie('dark_mode') ? vite()->asset('images/dark-logo.svg') : vite()->asset('images/logo.svg') }}"
                    id="logo-image" alt="{{ config('app.name') }}" />

                <img class="h-10 sm:hidden"
                    src="{{ request()->cookie('dark_mode') ? vite()->asset('images/mobile-dark-logo.svg') : vite()->asset('images/mobile-light-logo.svg') }}"
                    id="logo-image" alt="{{ config('app.name') }}" />
            @endif
        </a>
    </div>

    <div class="flex items-center gap-1.5 max-md:hidden">
        <!-- Mega Search Bar -->
        @include('admin::components.layouts.header.desktop.mega-search')

        <!-- Quick Creation Bar -->
        @include('admin::components.layouts.header.quick-creation')
    </div>

    <div class="flex items-center gap-2.5">
        <div class="md:hidden">
            <!-- Mega Search Bar -->
            @include('admin::components.layouts.header.mobile.mega-search')
        </div>

        <!-- Dark mode -->
        <v-dark>
            <div class="flex">
                <span
                    class="{{ request()->cookie('dark_mode') ? 'icon-light' : 'icon-dark' }} p-1.5 rounded-md text-2xl cursor-pointer transition-all hover:bg-gray-100 dark:hover:bg-gray-950"></span>
            </div>
        </v-dark>

        <div class="md:hidden">
            <!-- Quick Creation Bar -->
            @include('admin::components.layouts.header.quick-creation')
        </div>



        <div class="relative" id="notif-wrap">
            <button id="notif-bell" type="button"
                class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>

                <span id="notif-bell-count"
                    class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full"
                    style="display:none;">0</span>
            </button>

            <div id="notif-dd"
                class="hidden absolute right-0 mt-2 w-[360px] max-w-[90vw] rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900 z-[9999]">
                <div class="p-3 flex items-center justify-between">
                    <div class="flex flex-col">
                        <strong class="text-sm text-gray-800 dark:text-white">Notifications</strong>
                        <span id="notif-dd-sub" class="text-[11px] text-gray-500 dark:text-gray-400">Loading...</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" id="notif-dd-refresh"
                            class="rounded-md border border-gray-200 bg-white px-2 py-1 text-xs text-gray-700 hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                            Refresh
                        </button>

                        <button type="button" id="notif-dd-readall" class="text-xs text-brandColor hover:underline">
                            Mark all as read
                        </button>
                    </div>
                </div>

                <div id="notif-dd-list" class="px-3 pb-3 space-y-2 max-h-[320px] overflow-y-auto"></div>
            </div>
        </div>





        <!-- Admin profile -->
        <x-admin::dropdown position="bottom-{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'left' : 'right' }}">
            <x-slot:toggle>
                @php($user = auth()->guard('user')->user())

                @if ($user->image)
                    <button
                        class="flex h-9 w-9 cursor-pointer overflow-hidden rounded-full hover:opacity-80 focus:opacity-80">
                        <img src="{{ $user->image_url }}" class="h-full w-full object-cover" />
                    </button>
                @else
                    <button
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-pink-400 font-semibold leading-6 text-white">
                        {{ substr($user->name, 0, 1) }}
                    </button>
                @endif
            </x-slot>

            <!-- Admin Dropdown -->
            <x-slot:content class="mt-2 border-t-0 !p-0">
                <div
                    class="flex items-center gap-1.5 border border-x-0 border-b-gray-300 px-5 py-2.5 dark:border-gray-800">
                    <img src="{{ url('cache/logo.png') }}" width="24" height="24" />

                    <!-- Version -->
                    <p class="text-gray-400">
                        @lang('admin::app.layouts.app-version', ['version' => core()->version()])
                    </p>
                </div>

                <div class="grid gap-1 pb-2.5">
                    <a class="cursor-pointer px-5 py-2 text-base text-gray-800 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
                        href="{{ route('admin.user.account.edit') }}">
                        @lang('admin::app.layouts.my-account')
                    </a>

                    <!--Admin logout-->
                    <x-admin::form method="DELETE" action="{{ route('admin.session.destroy') }}" id="adminLogout">
                    </x-admin::form>

                    <a class="cursor-pointer px-5 py-2 text-base text-gray-800 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
                        href="{{ route('admin.session.destroy') }}"
                        onclick="event.preventDefault(); document.getElementById('adminLogout').submit();">
                        @lang('admin::app.layouts.sign-out')
                    </a>
                </div>
            </x-slot>
        </x-admin::dropdown>
    </div>
</header>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dark-template"
    >
        <div class="flex">
            <span
                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                :class="[isDarkMode ? 'icon-light' : 'icon-dark']"
                @click="toggle"
            ></span>
        </div>
    </script>

    <script type="module">
        app.component('v-dark', {
            template: '#v-dark-template',

            data() {
                return {
                    isDarkMode: {{ request()->cookie('dark_mode') ?? 0 }},

                    logo: "{{ vite()->asset('images/logo.svg') }}",

                    dark_logo: "{{ vite()->asset('images/dark-logo.svg') }}",
                };
            },

            methods: {
                toggle() {
                    this.isDarkMode = parseInt(this.isDarkModeCookie()) ? 0 : 1;

                    var expiryDate = new Date();

                    expiryDate.setMonth(expiryDate.getMonth() + 1);

                    document.cookie = 'dark_mode=' + this.isDarkMode + '; path=/; expires=' + expiryDate
                        .toGMTString();

                    document.documentElement.classList.toggle('dark', this.isDarkMode === 1);

                    if (this.isDarkMode) {
                        this.$emitter.emit('change-theme', 'dark');

                        document.getElementById('logo-image').src = this.dark_logo;
                    } else {
                        this.$emitter.emit('change-theme', 'light');

                        document.getElementById('logo-image').src = this.logo;
                    }
                },

                isDarkModeCookie() {
                    const cookies = document.cookie.split(';');

                    for (const cookie of cookies) {
                        const [name, value] = cookie.trim().split('=');

                        if (name === 'dark_mode') {
                            return value;
                        }
                    }

                    return 0;
                },
            },
        });
    </script>
@endPushOnce


@pushOnce('scripts')
<script>
(function () {

  const URL_INDEX   = "{{ route('admin.notifications.index') }}";
  const URL_READALL = "{{ route('admin.notifications.read_all') }}";
  const URL_READONE = "{{ url('admin/admin/notifications/read') }}";
  const CSRF = "{{ csrf_token() }}";

  function esc(str) {
    return String(str ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  async function loadNotifications() {
    const subEl   = document.getElementById('notif-dd-sub');
    const listEl  = document.getElementById('notif-dd-list');
    const badgeEl = document.getElementById('notif-bell-count');

    if (!subEl || !listEl) return;

    try {
      const res = await fetch(URL_INDEX + '?limit=10', {
        headers: { "Accept":"application/json", "X-Requested-With":"XMLHttpRequest" }
      });

      const data = await res.json();
      const unread = parseInt(data.unread_count || 0);

      subEl.textContent = unread ? `${unread} unread` : `All caught up`;

      if (badgeEl) {
        badgeEl.textContent = unread;
        badgeEl.style.display = unread ? 'inline-flex' : 'none';
      }

      const items = data.items || [];
      if (!items.length) {
        listEl.innerHTML = `<div class="text-xs text-gray-500 dark:text-gray-400">No notifications.</div>`;
        return;
      }

      listEl.innerHTML = items.map(n => `
        <div class="notif-item cursor-pointer rounded-md border border-gray-100 bg-gray-50 p-2 hover:bg-gray-100
                    dark:border-gray-800 dark:bg-gray-950"
             data-id="${n.id}" data-url="${esc(n.url || '')}">
          <div class="text-sm font-semibold text-gray-800 dark:text-white">${esc(n.title)}</div>
          ${n.body ? `<div class="mt-0.5 text-xs text-gray-700 dark:text-gray-200">${esc(n.body)}</div>` : ''}
          <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">${esc(n.created_at)}</div>
        </div>
      `).join('');

    } catch (e) {
      console.error(e);
      if (subEl) subEl.textContent = "Failed to load notifications";
    }
  }

  async function markAllRead() {
    await fetch(URL_READALL, {
      method: "POST",
      headers: {
        "Accept":"application/json",
        "X-Requested-With":"XMLHttpRequest",
        "X-CSRF-TOKEN": CSRF
      }
    });
    loadNotifications();
  }

  async function markOneRead(id) {
    await fetch(`${URL_READONE}/${id}`, {
      method: "POST",
      headers: {
        "Accept":"application/json",
        "X-Requested-With":"XMLHttpRequest",
        "X-CSRF-TOKEN": CSRF
      }
    });
  }

  // ✅ Event Delegation: شغال حتى لو Vue أعاد رسم الهيدر
  document.addEventListener('click', async function (e) {

    // toggle dropdown
    const bell = e.target.closest('#notif-bell');
    if (bell) {
      e.stopPropagation();
      const dd = document.getElementById('notif-dd');
      if (!dd) return;

      dd.classList.toggle('hidden');
      if (!dd.classList.contains('hidden')) loadNotifications();
      return;
    }

    // click refresh
    if (e.target.closest('#notif-dd-refresh')) {
      loadNotifications();
      return;
    }

    // click read all
    if (e.target.closest('#notif-dd-readall')) {
      markAllRead();
      return;
    }

    // click item
    const item = e.target.closest('#notif-dd-list .notif-item');
    if (item) {
      const id = item.dataset.id;
      const url = item.dataset.url || '#';

      await markOneRead(id);
      await loadNotifications();

      if (url && url !== '#') window.location.href = url;
      return;
    }

    // close if clicked outside
    const wrap = document.getElementById('notif-wrap');
    const dd = document.getElementById('notif-dd');
    if (dd && wrap && !wrap.contains(e.target)) {
      dd.classList.add('hidden');
    }
  });

  // initial badge + polling
  setTimeout(loadNotifications, 800);
  setInterval(loadNotifications, 15000);

})();
</script>
@endPushOnce

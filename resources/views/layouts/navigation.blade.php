@php
    $station = match (auth()->user()->role?->value) {
        'reception' => ['route' => route('reception'), 'label' => 'محطة الاستقبال', 'is' => 'reception', 'icon' => 'person_add'],
        'theory' => ['route' => route('case.search'), 'label' => 'بحث القضايا', 'is' => 'case.search', 'icon' => 'search'],
        'practical' => ['route' => route('case.search'), 'label' => 'بحث القضايا', 'is' => 'case.search', 'icon' => 'search'],
        'review' => ['route' => route('review'), 'label' => 'محطة المراجعة', 'is' => 'review', 'icon' => 'fact_check'],
        default => null,
    };
    $roleLabel = match (auth()->user()->role?->value) {
        'reception' => 'موظف الاستقبال',
        'theory' => 'موظف المختبر النظري',
        'practical' => 'موظف المختبر العملي',
        'review' => 'موظف المراجعة',
        default => 'موظف',
    };
@endphp
<aside
    x-bind:class="sidebarOpen ? 'translate-x-0' : 'translate-x-full'"
    class="fixed bottom-0 right-0 top-0 z-40 flex w-[18rem] max-w-[86vw] flex-col border-l border-outline-variant bg-surface-container-lowest shadow-xl transition-transform duration-200 lg:w-72 lg:translate-x-0 lg:shadow-none"
>
    <!-- Brand -->
    <div class="flex h-16 items-center gap-3 border-b border-outline-variant px-4">
        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high lg:hidden"
            @click="sidebarOpen = false"
            aria-label="إغلاق القائمة"
        >
            <span class="material-symbols-outlined">close</span>
        </button>
        <x-application-logo class="h-9 w-9 fill-current text-primary" />
        <div class="min-w-0">
            <div class="truncate text-sm font-bold text-on-surface">منصة اختبارات العمالة</div>
            <div class="truncate text-xs text-on-surface-variant">وزارة الموارد البشرية</div>
        </div>
    </div>

    <!-- Navigation Links (role-based, FR-01) -->
    <nav class="flex-1 space-y-1 overflow-y-auto p-3">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard">
            لوحة التحكم
        </x-nav-link>
        @if($station)
            <x-nav-link :href="$station['route']" :active="request()->routeIs($station['is'])" :icon="$station['icon']">
                {{ __($station['label']) }}
            </x-nav-link>
        @endif
        @if(auth()->user()->role?->value === 'reception')
            <x-nav-link :href="route('case.search')" :active="request()->routeIs('case.search')" icon="search">
                بحث القضايا
            </x-nav-link>
        @endif
    </nav>

    <!-- User -->
    <div class="border-t border-outline-variant p-3">
        <div class="flex items-center gap-3 rounded-xl bg-surface-container px-3 py-2.5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-on-primary">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </span>
            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-bold text-on-surface">{{ Auth::user()->name }}</div>
                <div class="truncate text-xs text-on-surface-variant">{{ $roleLabel }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-error transition hover:bg-error-container/40 focus:outline-none">
                <span class="material-symbols-outlined text-[22px]">logout</span>
                تسجيل الخروج
            </button>
        </form>
    </div>
</aside>

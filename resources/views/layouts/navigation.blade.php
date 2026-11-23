@php
    $station = match (auth()->user()->role?->value) {
        'reception' => ['route' => route('reception'), 'label' => 'محطة الاستقبال', 'is' => 'reception'],
        'theory' => null,
        'practical' => null,
        'review' => ['route' => route('review'), 'label' => 'محطة المراجعة', 'is' => 'review'],
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
<nav x-data="{ open: false }" class="bg-white shadow-sm border-b border-line">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-9 w-9 fill-current text-primary" />
                    <span class="hidden text-base font-bold text-primary-dark sm:inline">منصة اختبارات العمالة</span>
                </a>

                <!-- Navigation Links (role-based, FR-01) -->
                <div class="hidden items-center gap-1 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('لوحة التحكم') }}
                    </x-nav-link>
                    @if($station)
                        <x-nav-link :href="$station['route']" :active="request()->routeIs($station['is'])">
                            {{ __($station['label']) }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden flex-col items-end sm:flex">
                    <span class="text-sm font-bold text-ink">{{ Auth::user()->name }}</span>
                    <span class="dga-badge bg-primary-pastel/60 text-primary-dark">{{ $roleLabel }}</span>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:block">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="dga-btn-outline px-4 py-1.5 text-xs font-semibold">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2">
                                <div class="text-sm font-bold text-ink">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-muted">{{ $roleLabel }}</div>
                            </div>
                            <div class="border-t border-line"></div>

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('الملف الشخصي') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        class="text-danger"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('تسجيل الخروج') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center rounded-lg p-2 text-muted hover:bg-surface focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-line sm:hidden">
        <div class="space-y-1 px-4 pb-3 pt-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('لوحة التحكم') }}
            </x-responsive-nav-link>
            @if($station)
                <x-responsive-nav-link :href="$station['route']" :active="request()->routeIs($station['is'])">
                    {{ __($station['label']) }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-line pb-2 pt-4">
            <div class="px-4">
                <div class="text-base font-bold text-ink">{{ Auth::user()->name }}</div>
                <div class="text-sm text-muted">{{ $roleLabel }}</div>
            </div>

            <div class="mt-3 space-y-1 px-4">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('الملف الشخصي') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('تسجيل الخروج') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

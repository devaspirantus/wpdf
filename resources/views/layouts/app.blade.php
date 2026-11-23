<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $roleLabel = match (auth()->user()?->role?->value) {
                'reception' => 'موظف الاستقبال',
                'theory' => 'موظف المختبر النظري',
                'practical' => 'موظف المختبر العملي',
                'review' => 'موظف المراجعة',
                default => 'موظف',
            };
        @endphp
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-surface">
            @include('layouts.navigation')

            <div
                x-show="sidebarOpen"
                x-transition.opacity
                x-cloak
                class="fixed inset-0 z-30 bg-navy/35 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
            ></div>

            <div class="lg:pr-72">
                <!-- Page Header -->
                <header class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between border-b border-outline-variant bg-surface-container-lowest/95 px-4 shadow-sm backdrop-blur lg:right-72 lg:px-6">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-on-surface hover:bg-surface-container-high lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="فتح القائمة"
                    >
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    @if (!empty($header))
                        <h1 class="text-lg font-bold text-on-surface">{{ $header }}</h1>
                    @else
                        <span class="text-lg font-bold text-on-surface">{{ config('app.name', 'Laravel') }}</span>
                    @endif
                    <div class="flex items-center gap-2 md:gap-4">
                        <span class="hidden text-sm text-on-surface-variant sm:inline">{{ now()->locale('ar')->format('j F Y') }}</span>
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium text-on-surface transition hover:bg-surface-container-high focus:outline-none">
                                    <span class="material-symbols-outlined text-[22px]">account_circle</span>
                                    <span class="hidden md:inline">{{ Auth::user()->name }}</span>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2">
                                    <div class="text-sm font-bold text-on-surface">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ $roleLabel ?? '' }}</div>
                                </div>
                                <div class="border-t border-outline-variant"></div>

                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('الملف الشخصي') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <x-dropdown-link :href="route('logout')"
                                            class="text-error"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('تسجيل الخروج') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="px-4 py-6 pt-22 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>

                <footer class="px-6 pb-6 text-center text-xs text-on-surface-variant">
                    © {{ now()->year }} — جميع الحقوق محفوظة
                </footer>
            </div>
        </div>

        @livewireScripts
    </body>
</html>

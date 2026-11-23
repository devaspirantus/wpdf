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
        <div class="min-h-screen bg-surface">
            @include('layouts.navigation')

            <div class="pr-72">
                <!-- Page Header -->
                <header class="fixed left-0 right-72 top-0 z-30 flex h-16 items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-6 shadow-sm">
                    @if (!empty($header))
                        <h1 class="text-lg font-bold text-on-surface">{{ $header }}</h1>
                    @else
                        <span class="text-lg font-bold text-on-surface">{{ config('app.name', 'Laravel') }}</span>
                    @endif
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-on-surface-variant">{{ now()->locale('ar')->format('j F Y') }}</span>
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
                <main class="px-6 py-6 pt-22">
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

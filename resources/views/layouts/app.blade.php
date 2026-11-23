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
        <div class="min-h-screen bg-surface">
            <div class="bg-navy">
                <div class="mx-auto flex h-9 max-w-7xl items-center justify-between px-4 text-xs text-white/80 sm:px-6 lg:px-8">
                    <span>منصة اختبارات العمالة — وزارة الموارد البشرية</span>
                    <span>{{ now()->locale('ar')->format('j M Y') }}</span>
                </div>
            </div>

            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm">
                    <div class="mx-auto max-w-7xl border-b border-line px-4 pb-4 pt-6 sm:px-6 lg:px-8">
                        <h1 class="text-xl font-bold text-primary-dark sm:text-2xl">{{ $header }}</h1>
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>

            <footer class="mx-auto max-w-7xl px-4 pb-6 text-center text-xs text-muted sm:px-6 lg:px-8">
                © {{ now()->year }} — جميع الحقوق محفوظة
            </footer>
        </div>

        @livewireScripts
    </body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-surface px-4 py-10">
            <div class="absolute inset-x-0 top-0 h-64 bg-navy"></div>
            <div class="absolute -left-24 -top-24 h-64 w-64 rounded-full border-[48px] border-primary/20"></div>

            <div class="relative mb-6 flex flex-col items-center gap-3 text-white">
                <a href="/">
                    <x-application-logo class="h-20 w-20 fill-current text-white" />
                </a>
                <div class="text-center">
                    <div class="text-xl font-bold">منصة اختبارات العمالة</div>
                    <div class="mt-1 text-sm text-white/75">دخول الموظفين المصرح لهم</div>
                </div>
            </div>

            <div class="dga-card relative w-full max-w-md shadow-xl">
                <div class="dga-card-body p-6 sm:p-8">{{ $slot }}</div>
            </div>
        </div>
    </body>
</html>

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
        <div class="flex min-h-screen flex-col items-center justify-center bg-surface px-4 py-10">
            <div class="mb-6 flex flex-col items-center gap-3">
                <a href="/">
                    <x-application-logo class="h-20 w-20 fill-current text-primary" />
                </a>
                <div class="text-center">
                    <div class="text-lg font-bold text-primary-dark">منصة اختبارات العمالة</div>
                    <div class="text-sm text-muted">دخول الموظفين المصرح لهم</div>
                </div>
            </div>

            <div class="dga-card w-full max-w-md">
                <div class="dga-card-body">{{ $slot }}</div>
            </div>
        </div>
    </body>
</html>

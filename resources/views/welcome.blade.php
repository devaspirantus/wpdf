<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }} — منصة اختبارات العمالة</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface">
        <header class="relative overflow-hidden bg-navy">
            <div class="absolute inset-y-0 left-0 w-1/3 bg-primary/15"></div>
            <div class="relative mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <span class="block text-sm font-medium text-white/90">منصة الاختبارات المعتمدة</span>
                @auth
                    <a href="{{ url('/dashboard') }}" class="dga-btn bg-white !text-navy hover:bg-white/90">
                        لوحة التحكم
                    </a>
                @else
                    <a href="{{ route('login') }}" class="dga-btn bg-white !text-navy hover:bg-white/90">
                        دخول الموظفين
                    </a>
                @endauth
            </div>
        </header>

        <main class="mx-auto w-full max-w-5xl px-4 py-12 sm:px-6 sm:py-16">
            <section class="text-center">
                <div class="mx-auto mb-8 w-fit">
                    <div class="flex h-28 w-28 items-center justify-center rounded-full bg-primary/10 ring-8 ring-primary/5">
                        <x-application-logo class="h-20 w-20 fill-current text-primary" />
                    </div>
                </div>
                <span class="dga-badge bg-primary/10 text-primary">تسلسل محطات واحد ومنظم</span>
                <h1 class="mt-4 text-3xl font-bold text-navy sm:text-5xl">منصة اختبارات العمالة</h1>
                <p class="mx-auto mt-4 max-w-2xl text-base leading-8 text-muted">
                    نظام موحد لإدارة دورة اختبار العمالة عبر المحطات الأربع: الاستقبال، ثم مختبر
                    النظري، ثم المختبر العملي، ثم مراجعة البيانات — وينتهي بإصدار تقرير موحد لكل عامل في ملف PDF واحد.
                </p>
            </section>

            <section class="mt-12">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['1', 'person_add', 'الاستقبال', 'إنشاء الملف ورفع صور الهوية والتذكرة'],
                        ['2', 'menu_book', 'المختبر النظري', 'توثيق حضور العامل بصورة معتمدة'],
                        ['3', 'construction', 'المختبر العملي', 'التقييم بالتوقيعين وقفل النموذج'],
                        ['4', 'fact_check', 'رفع البيانات', 'المراجعة والإرجاع ثم إصدار PDF'],
                    ] as [$number, $icon, $title, $description])
                        <article class="dga-card group">
                            <div class="dga-card-body p-5">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <span class="material-symbols-outlined">{{ $icon }}</span>
                                    </span>
                                    <span class="text-2xl font-bold text-primary/25">{{ $number }}</span>
                                </div>
                                <h2 class="font-bold text-navy">{{ $title }}</h2>
                                <p class="mt-2 text-sm leading-6 text-muted">{{ $description }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>

        <footer class="border-t border-line bg-white">
            <div class="mx-auto max-w-5xl px-4 py-6 text-center text-sm text-muted sm:px-6">
                جميع الحقوق محفوظة — منصة اختبارات العمالة
            </div>
        </footer>
    </body>
</html>

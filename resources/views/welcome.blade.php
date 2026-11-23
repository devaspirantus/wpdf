<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }} — منصة اختبارات العمالة</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface">
        <header class="bg-navy">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
                <span class="block text-sm font-medium text-white">حكومة المملكة العربية السعودية</span>
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

        <main class="mx-auto w-full max-w-3xl px-4 py-16 sm:px-6 sm:py-24">
            <section class="text-center">
                <div class="mx-auto mb-8 w-fit">
                    <x-application-logo class="h-24 w-24 fill-current text-primary" />
                </div>
                <h1 class="text-3xl font-bold text-navy sm:text-4xl">منصة اختبارات العمالة</h1>
                <p class="mx-auto mt-4 max-w-xl text-base leading-8 text-muted">
                    نظام موحد لإدارة دورة اختبار العمالة عبر المحطات الأربع: الاستقبال، ثم مختبر
                    النظري، ثم المختبر العملي، ثم مراجعة البيانات — وينتهي بإصدار تقرير موحد لكل عامل.
                </p>
            </section>

            <section class="mt-12">
                <div class="dga-card">
                    <div class="dga-card-body grid gap-4 sm:grid-cols-3">
                        <div class="text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary">1</div>
                            <h2 class="text-base font-semibold text-navy">الاستقبال</h2>
                            <p class="mt-1 text-sm text-muted">فتح ملف العامل ورفع المستندات الأساسية.</p>
                        </div>
                        <div class="text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary">2</div>
                            <h2 class="text-base font-semibold text-navy">اختبار النظري</h2>
                            <p class="mt-1 text-sm text-muted">تقييم المعرفة وفق معايير ثابتة موحدة.</p>
                        </div>
                        <div class="text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary">3</div>
                            <h2 class="text-base font-semibold text-navy">العملية والمراجعة</h2>
                            <p class="mt-1 text-sm text-muted">الاختبار العملي ثم المراجعة النهائية وإصدار التقرير.</p>
                        </div>
                    </div>
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

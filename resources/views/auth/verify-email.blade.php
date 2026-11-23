<x-guest-layout>
    <div class="mb-4 text-sm text-muted">
        {{ __('شكراً لتسجيلك! قبل بدء الاستخدام، يمكنك التحقق من بريدك الإلكتروني بالنقر على الرابط الذي أرسلناه للتو. إذا لم تستلم البريد، يسعدنا إرسال بريد آخر.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-success">
            {{ __('تم إرسال رابط تحقق جديد إلى البريد الإلكتروني الذي أوردته أثناء التسجيل.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('إعادة إرسال بريد التحقق') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-primary-dark hover:text-ink rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30">
                {{ __('تسجيل الخروج') }}
            </button>
        </form>
    </div>
</x-guest-layout>

<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-navy">تسجيل الدخول</h1>
        <p class="mt-1 text-sm leading-6 text-muted">أدخل بيانات حسابك للمتابعة إلى المحطة المخصصة لك</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('البريد الإلكتروني')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('كلمة المرور')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-line text-primary shadow-sm focus:ring-primary/30" name="remember">
                <span class="ms-2 text-sm text-muted">{{ __('تذكّر بي') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-primary-dark hover:text-ink rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30" href="{{ route('password.request') }}">
                    {{ __('هل نسيت كلمة المرور؟') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('تسجيل الدخول') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<section>
    <header>
        <h2 class="text-lg font-bold text-ink">
            {{ __('معلومات الملف الشخصي') }}
        </h2>

        <p class="mt-1 text-sm text-muted">
            {{ __('حدّث معلومات ملفك الشخصي وبريدك الإلكتروني.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('الاسم')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('البريد الإلكتروني')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-ink">
                        {{ __('بريدك الإلكتروني غير مُتحقَّق منه.') }}

                        <button form="send-verification" class="underline text-sm text-primary-dark hover:text-ink rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30">
                            {{ __('انقر هنا لإعادة إرسال بريد التحقق.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-success">
                            {{ __('تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('حفظ') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-muted"
                >{{ __('تم الحفظ.') }}</p>
            @endif
        </div>
    </form>
</section>

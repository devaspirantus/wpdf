<div x-data="cameraCapture"
     class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 space-y-4" dir="rtl">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">{{ $label }}</h3>

            @if ($existing)
                <span class="inline-flex items-center rounded-md bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                    تم الالتقاط ✓
                </span>
            @endif
        </div>

        <video x-show="!captured && !cameraError" x-ref="video" autoplay playsinline muted
               class="w-full rounded-lg bg-black aspect-video"></video>

        <div x-show="captured">
            <img x-ref="preview" alt="معاينة الصورة"
                 class="w-full rounded-lg aspect-video object-cover bg-gray-100" />
        </div>

        <div x-show="cameraError" class="rounded-md bg-red-50 border border-red-200 p-3 text-red-800 text-sm">
            <span x-text="cameraError"></span>
        </div>

        <div class="flex items-center gap-3">
            <template x-if="!captured">
                <button type="button" @click="capture()" x-show="hasStream"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md disabled:opacity-50"
                        :disabled="!hasStream">
                    التقاط الصورة
                </button>
            </template>

            <template x-if="captured">
                <div class="flex items-center gap-3">
                    <button type="button" @click="retake()"
                            class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold rounded-md">
                        إعادة الالتقاط
                    </button>
                    <button type="button" @click="confirmCapture()"
                            :disabled="!uploaded"
                            wire:target="capture" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md disabled:opacity-50">
                        <span wire:loading.remove>تأكيد وحفظ</span>
                        <span wire:loading>جارٍ الحفظ...</span>
                    </button>
                </div>
            </template>
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('image')" />
    </div>
</div>

@script
<script>
function cameraCapture() {
    return {
        stream: null,
        hasStream: false,
        captured: false,
        uploaded: false,
        cameraError: '',

        init() {
            this.startCamera();
        },

        async startCamera() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false,
                });
                this.cameraError = '';
                this.$refs.video.srcObject = this.stream;
                this.hasStream = true;
            } catch (err) {
                this.hasStream = false;
                this.cameraError = 'تعذّر الوصول إلى الكاميرا. يرجى منح إذن استخدام الكاميرا ثم أعد المحاولة.';
            }
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach((track) => track.stop());
                this.stream = null;
                this.hasStream = false;
            }
        },

        async capture() {
            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            this.$refs.preview.src = canvas.toDataURL('image/png');

            try {
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
                const file = new File([blob], `capture-${Date.now()}.png`, { type: 'image/png' });
                this.captured = true;
                this.stopCamera();
                await $wire.upload('image', file);
                this.uploaded = true;
            } catch (err) {
                this.cameraError = 'حدث خطأ أثناء التقاط الصورة، يرجى إعادة المحاولة.';
            }
        },

        retake() {
            this.captured = false;
            this.uploaded = false;
            $wire.set('image', null);
            this.startCamera();
        },

        confirmCapture() {
            $wire.capture();
        },

        destroy() {
            this.stopCamera();
        },
    };
}
</script>
@endscript

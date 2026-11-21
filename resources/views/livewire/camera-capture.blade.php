<div x-data="{
        stream: null,
        captured: false,
        cameraError: '',
        rawError: '',
        showErrorDetails: false,
        preview: '',

        startCamera() {
            this.cameraError = '';
            this.rawError = '';
            this.showErrorDetails = false;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.cameraError = 'المتصفح لا يدعم الوصول إلى الكاميرا في هذا السياق (يتطلب اتصالاً آمناً).';
                this.rawError = 'NotSupportedError: navigator.mediaDevices.getUserMedia is not available';
                return;
            }

            const getUserMedia = navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices);

            getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .catch(() => getUserMedia({ video: true, audio: false }))
                .then((s) => {
                    this.stream = s;
                    this.$refs.video.srcObject = s;
                })
                .catch((err) => {
                    this.stream = null;
                    const name = (err && err.name) || 'Error';
                    const message = (err && err.message) || 'unknown error';
                    this.rawError = name + ': ' + message;
                    if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
                        this.cameraError = 'تعذّر الوصول إلى الكاميرا. يرجى منح إذن استخدام الكاميرا ثم أعد المحاولة.';
                    } else {
                        this.cameraError = this.rawError;
                    }
                });
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach((t) => t.stop());
                this.stream = null;
            }
        },

        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0);
            this.preview = canvas.toDataURL('image/png');
            this.captured = true;
            this.stopCamera();
        },

        retake() {
            this.captured = false;
            this.preview = '';
            this.startCamera();
        },

        confirmCapture() {
            if (!this.preview) {
                return;
            }
            this.$wire.saveImage(this.preview);
        },

        beforeDestroy() {
            this.stopCamera();
        },
    }"
    x-init="startCamera()"
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

        <template x-if="!captured && !cameraError">
            <video x-ref="video" autoplay playsinline muted
                   class="w-full rounded-lg bg-black aspect-video"></video>
        </template>

        <div x-show="captured" x-cloak>
            <img :src="preview" alt="معاينة الصورة"
                 class="w-full rounded-lg aspect-video object-cover bg-gray-100" />
        </div>

        <div x-show="cameraError" x-cloak class="rounded-md bg-red-50 border border-red-200 p-3 text-red-800 text-sm space-y-2">
            <span x-text="cameraError"></span>
            <button type="button" x-show="rawError" x-on:click="showErrorDetails = !showErrorDetails"
                    class="text-xs underline text-red-700" x-cloak>
                <span x-text="showErrorDetails ? 'إخفاء تفاصيل الخطأ' : 'تفاصيل الخطأ'"></span>
            </button>
            <div x-show="showErrorDetails && rawError" x-cloak class="text-xs font-mono break-all text-red-900" dir="ltr">
                <span x-text="rawError"></span>
            </div>
        </div>

        <template x-if="!captured">
            <button type="button" x-on:click="capturePhoto()" :disabled="!stream"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md disabled:opacity-50">
                التقاط الصورة
            </button>
        </template>

        <template x-if="captured">
            <div class="flex items-center gap-3" x-cloak>
                <button type="button" x-on:click="retake()"
                        class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold rounded-md">
                    إعادة الالتقاط
                </button>
                <button type="button" x-on:click="confirmCapture()"
                        wire:target="saveImage" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md disabled:opacity-50">
                    <span wire:loading.remove>تأكيد وحفظ</span>
                    <span wire:loading wire:target="saveImage" x-cloak>جارٍ الحفظ...</span>
                </button>
            </div>
        </template>

        <x-input-error class="mt-2" :messages="$errors->get('imageData')" />
    </div>
    <canvas x-ref="canvas" class="hidden"></canvas>
</div>
